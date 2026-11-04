-- schema.sql
-- Purpose: Builds the pet_adoption database from scratch.
-- Table order: employees, pets, adopters, rejection_reasons, applications.
-- Then three views: pending_applications, approved_applications, rejected_applications.

-- 1. RESET
-- Deletes the old database, so this script can run again from scratch.
DROP DATABASE IF EXISTS pet_adoption;
CREATE DATABASE pet_adoption;
USE pet_adoption;

-- 2. EMPLOYEE TABLE
-- employee_id is a badge number the store assigns, so it isn't numbered automatically (Business rule #12).
-- Only Adoption staff can handle applications. The app checks this, not the table (Business rule #22).
CREATE TABLE employees (
    employee_id  INT PRIMARY KEY,
    first_name   VARCHAR(50) NOT NULL,
    last_name    VARCHAR(50) NOT NULL,
    department   ENUM('Adoption', 'Sales') NOT NULL
);

-- 3. PETS TABLE
-- The app changes a pet's status only through applications (Business rules #16 and #19).
-- Adopted pets are never deleted, so they keep their pet_id for good (Business rule #28).
CREATE TABLE pets (
    pet_id        INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(50) NOT NULL,
    species       ENUM('Dog', 'Cat', 'Rabbit', 'Bird', 'Other')
                  NOT NULL,
    breed         VARCHAR(50),
    birth_date    DATE,
    estimated_age TINYINT UNSIGNED,                        -- Age on the day it arrived, typed only when the birth date is unknown (Business rule #31).
    status        ENUM('Available', 'Pending', 'Adopted', 'Returned')
                  NOT NULL DEFAULT 'Available',
    date_added    DATE NOT NULL DEFAULT (CURRENT_DATE),
    adoption_fee  DECIMAL(6,2) NOT NULL,
    vaccinated    BOOLEAN NOT NULL DEFAULT FALSE,          -- 1 = yes, 0 = no.
    CONSTRAINT chk_pets_fee CHECK (adoption_fee >= 0),     -- Business rule #11
    -- An estimate is 0 to 50 years, a pet can't have both ages, and dogs, cats, rabbits and birds need one (Business rule #31).
    CONSTRAINT chk_pets_estimate CHECK (estimated_age <= 50),
    CONSTRAINT chk_pets_one_age CHECK (birth_date IS NULL OR estimated_age IS NULL),
    CONSTRAINT chk_pets_age_needed CHECK (species = 'Other'
        OR birth_date IS NOT NULL OR estimated_age IS NOT NULL),
    -- The fee must be the set price for the pet's kind and its age on the day it arrived (Business rules #29 and #30).
    -- COALESCE uses the age from the birth date, or the estimate when there is no birth date (Business rule #31).
    -- Other animals have no set price, so staff type $10 to $75 (Business rule #32).
    CONSTRAINT chk_pets_fee_by_age CHECK (
        (species = 'Other' AND adoption_fee BETWEEN 10 AND 75)
        OR (species <> 'Other' AND adoption_fee = CASE
            WHEN COALESCE(TIMESTAMPDIFF(YEAR, birth_date, date_added), estimated_age) < 1
                THEN CASE species WHEN 'Dog' THEN 250 WHEN 'Cat' THEN 150 WHEN 'Rabbit' THEN 75 WHEN 'Bird' THEN 100 END
            WHEN COALESCE(TIMESTAMPDIFF(YEAR, birth_date, date_added), estimated_age) <= 6
                THEN CASE species WHEN 'Dog' THEN 175 WHEN 'Cat' THEN 100 WHEN 'Rabbit' THEN 50 WHEN 'Bird' THEN 60 END
            ELSE CASE species WHEN 'Dog' THEN 100 WHEN 'Cat' THEN 50 WHEN 'Rabbit' THEN 25 WHEN 'Bird' THEN 30 END
        END)
    )
);

-- 4. ADOPTERS TABLE
-- The store assigns each adopter_id, like 1 or -110989116, so it isn't numbered automatically (Business rule #12).
-- The app checks the age 18+ rule when someone applies, not here (Business rule #14).
CREATE TABLE adopters (
    adopter_id     INT PRIMARY KEY,
    first_name     VARCHAR(50) NOT NULL,
    last_name      VARCHAR(50) NOT NULL,
    phone          VARCHAR(20) NOT NULL,                   -- Text, so it keeps the format, like (718) 555-0123.
    email          VARCHAR(100) NOT NULL,
    address        VARCHAR(150) NOT NULL,
    date_of_birth  DATE NOT NULL,
    date_joined    DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT uq_adopters_email UNIQUE (email)            -- Business rule #7
);

-- 5. REJECTION REASONS TABLE
-- Each reason is stored once, with the business rule it breaks (Business rule #25).
-- These 8 rows are part of the design, so they're added here, not in seed.sql.
-- Their IDs are 1 to 8 in this order. Reasons 1 to 6 are rejected right away, and 7 and 8 after staff review.
CREATE TABLE rejection_reasons (
    reason_id    INT AUTO_INCREMENT PRIMARY KEY,
    reason       VARCHAR(100) NOT NULL,
    rule_number  INT NOT NULL,
    CONSTRAINT uq_rejection_reasons_reason UNIQUE (reason)
);

INSERT INTO rejection_reasons (reason, rule_number) VALUES
('Adopter is under 18',                        14),
('Pet is not vaccinated',                      13),
('Pet is already adopted',                     13),
('Pet already has a pending application',      13),
('Pet was returned and is still in care',      13),
('Adopter already has a pending application',  15),
('Did not pass the home visit',                26),
('Landlord does not allow pets',               27);

-- 6. APPLICATION TABLE
-- Each application links one adopter, one pet and one employee (Business rules #1, #3 and #20).
-- ON DELETE RESTRICT blocks deleting an adopter, pet or employee that has applications (Business rules #17 and #28).
-- An application that breaks Business rule #13, #14 or #15 is saved as Rejected with its reason (Business rule #25).
CREATE TABLE applications (
    application_id    INT AUTO_INCREMENT PRIMARY KEY,
    adopter_id        INT NOT NULL,
    pet_id            INT NOT NULL,
    employee_id       INT NOT NULL,
    application_date  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status            ENUM('Pending', 'Approved', 'Rejected')
                      NOT NULL DEFAULT 'Pending',
    rejection_reason_id INT,
    CONSTRAINT fk_applications_adopter
        FOREIGN KEY (adopter_id) REFERENCES adopters (adopter_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_applications_pet
        FOREIGN KEY (pet_id) REFERENCES pets (pet_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_applications_employee
        FOREIGN KEY (employee_id) REFERENCES employees (employee_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_applications_reason
        FOREIGN KEY (rejection_reason_id) REFERENCES rejection_reasons (reason_id)
        ON DELETE RESTRICT,
    CONSTRAINT chk_applications_reason CHECK (
        (status = 'Rejected' AND rejection_reason_id IS NOT NULL)
        OR (status <> 'Rejected' AND rejection_reason_id IS NULL)
    )                                                                -- Only Rejected applications have a reason (Business rule #25).
);

-- 7. VIEWS (the three application result tables: pending, approved and rejected)
-- A view is a saved SELECT. It works like a table, but it holds no data of its own.
-- Each view keeps one status and shows names instead of ID numbers. a, ad, p and e are short names for the tables.
-- INNER JOIN works here because every application has an adopter, a pet and an employee.
-- When an application's status changes, it moves to the matching view by itself (Business rule #16).

-- adopter_phone is there so staff can set up the home visit (Business rule #26).
CREATE OR REPLACE VIEW pending_applications AS
SELECT a.application_id,
       a.application_date,
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,
       ad.phone AS adopter_phone,
       p.name AS pet_name,
       p.species,
       CONCAT(e.first_name, ' ', e.last_name) AS handled_by
FROM applications a
INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id
INNER JOIN pets p ON p.pet_id = a.pet_id
INNER JOIN employees e ON e.employee_id = a.employee_id
WHERE a.status = 'Pending'
ORDER BY a.application_id;

-- pet_status shows Returned if the pet came back (Business rule #18).
CREATE OR REPLACE VIEW approved_applications AS
SELECT a.application_id,
       a.application_date,
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,
       p.name AS pet_name,
       p.species,
       p.adoption_fee,
       p.status AS pet_status,
       CONCAT(e.first_name, ' ', e.last_name) AS handled_by
FROM applications a
INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id
INNER JOIN pets p ON p.pet_id = a.pet_id
INNER JOIN employees e ON e.employee_id = a.employee_id
WHERE a.status = 'Approved'
ORDER BY a.application_id;

-- Adds the reason and the rule each application broke (Business rule #25).
CREATE OR REPLACE VIEW rejected_applications AS
SELECT a.application_id,
       a.application_date,
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,
       p.name AS pet_name,
       r.reason,
       CONCAT('Business rule #', r.rule_number) AS rule_broken,
       CONCAT(e.first_name, ' ', e.last_name) AS handled_by
FROM applications a
INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id
INNER JOIN pets p ON p.pet_id = a.pet_id
INNER JOIN employees e ON e.employee_id = a.employee_id
INNER JOIN rejection_reasons r ON r.reason_id = a.rejection_reason_id
WHERE a.status = 'Rejected'
ORDER BY a.application_id;
