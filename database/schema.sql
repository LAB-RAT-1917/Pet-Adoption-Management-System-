-- schema.sql
-- Project: Pet Adoption Management System (CIS 344 Group Project).
-- Purpose: Builds the pet_adoption database from scratch.
-- Table order: employees, pets, adopters, rejection_reasons, applications.
-- Then three views: pending_applications, approved_applications, rejected_applications.

-- 1. RESET
-- Deletes an old database with the same name and makes a new, empty one.
DROP DATABASE IF EXISTS pet_adoption;
CREATE DATABASE pet_adoption;
USE pet_adoption;

-- 2. EMPLOYEE TABLE
-- One row per staff member.
-- The store assigns each employee_id as a badge number, so it is not numbered automatically (Business rule #12).
-- Name and department are required (Business rule #23).
-- Department is Adoption or Sales (Business rule #24).
-- Only Adoption staff can handle applications (Business rule #22).
-- The app checks this, not the table.
-- Applications point here using employee_id.
CREATE TABLE employees (
    employee_id  INT PRIMARY KEY,                -- Badge number the store assigns, like 1843 (Business rule #12).
    first_name   VARCHAR(50) NOT NULL,
    last_name    VARCHAR(50) NOT NULL,
    department   ENUM('Adoption', 'Sales') NOT NULL
);

-- 3. PETS TABLE
-- One row per pet in the store.
-- The app handles status changes (Business rules #16 and #19).
-- Adopted pets are never removed. Adoption only changes the status, and the pet keeps its pet_id for good (Business rule #28).
-- Applications point here using pet_id.
CREATE TABLE pets (
    pet_id        INT AUTO_INCREMENT PRIMARY KEY,          -- Unique ID, numbered automatically (Business rule #12).
    name          VARCHAR(50) NOT NULL,                    -- A pet is required to have a name (Business rule #5).
    species       ENUM('Dog', 'Cat', 'Rabbit', 'Bird', 'Other')
                  NOT NULL,                                -- Kind of animal, set list only (Business rule #10).
    breed         VARCHAR(50),                             -- Optional, an animal's breed could be unknown.
    birth_date    DATE,                                    -- Optional, the app works out age from it.
    status        ENUM('Available', 'Pending', 'Adopted', 'Returned')
                  NOT NULL DEFAULT 'Available',            -- New pets start as Available (Business rule #8).
    date_added    DATE NOT NULL DEFAULT (CURRENT_DATE),    -- Filled in with today's date (Business rule #12).
    adoption_fee  DECIMAL(6,2) NOT NULL,                   -- Price to adopt, up to $9999.99
    vaccinated    BOOLEAN NOT NULL DEFAULT FALSE,          -- 1 = yes, 0 = no, starts as no.
    CONSTRAINT chk_pets_fee CHECK (adoption_fee >= 0)      -- Fee can't be negative (Business rule #11).
);

-- 4. ADOPTERS TABLE 
-- One row per customer who may adopt.
-- Holds personal data, so the app shows only name and phone in lists.
-- Age 18+ is checked by the app when a customer applies (Business rule #14).
-- The store assigns each adopter_id, so it is not numbered automatically (Business rule #12).
-- Applications point here using adopter_id.
CREATE TABLE adopters (
    adopter_id     INT PRIMARY KEY,                        -- ID the store assigns, like 1 or -110989116 (Business rule #12).
    first_name     VARCHAR(50) NOT NULL,                   -- Required (Business rule #6).
    last_name      VARCHAR(50) NOT NULL,                   -- Required (Business rule #6).
    phone          VARCHAR(20) NOT NULL,                   -- Stored as text, like (718) 555-0123
    email          VARCHAR(100) NOT NULL,                  -- Required, and no repeats (Business rules #6 and #7).
    address        VARCHAR(150) NOT NULL,                  -- Street, city, zip code, and state.
    date_of_birth  DATE NOT NULL,                          -- The app works out age from it (Business rule #14).
    date_joined    DATE NOT NULL DEFAULT (CURRENT_DATE),   -- Filled in with today's date (Business rule #12).
    CONSTRAINT uq_adopters_email UNIQUE (email)            -- No two adopters share an email (Business rule #7).
);

-- 5. REJECTION REASONS TABLE
-- One row per reason an application can be rejected.
-- Each reason is stored once, with the business rule it breaks (Business rule #25).
-- These rows are part of the design, so they are added here, not in seed.sql.
-- Applications point here using reason_id.
CREATE TABLE rejection_reasons (
    reason_id    INT AUTO_INCREMENT PRIMARY KEY,           -- Unique ID, numbered automatically (Business rule #12).
    reason       VARCHAR(100) NOT NULL,                    -- What went wrong, in plain words.
    rule_number  INT NOT NULL,                             -- The business rule it breaks, like 14.
    CONSTRAINT uq_rejection_reasons_reason UNIQUE (reason) -- Each reason is listed only once.
);

INSERT INTO rejection_reasons (reason, rule_number) VALUES
('Adopter is under 18',                        14),   -- 1, rejected right away
('Pet is not vaccinated',                      13),   -- 2, rejected right away
('Pet is already adopted',                     13),   -- 3, rejected right away
('Pet already has a pending application',      13),   -- 4, rejected right away
('Pet was returned and is still in care',      13),   -- 5, rejected right away (also Business rule #18)
('Adopter already has a pending application',  15),   -- 6, rejected right away
('Did not pass the home visit',                26),   -- 7, rejected after staff review
('Landlord does not allow pets',               27);   -- 8, rejected after staff review

-- 6. APPLICATION TABLE 
-- One row per adoption application.
-- Links one adopter, one pet and one employee.
-- Foreign keys block deleting an adopter, pet or employee who has applications (Business rule #17).
-- If an application breaks Business rule #13, #14, or #15, the app saves it as Rejected with a reason (Business rule #25).
-- Staff can also reject a Pending application after review (Business rules #26 and #27).
-- Only Adoption staff appear in the Handled by list (Business rule #22).
-- Approve and reject run as a transaction (Business rule #16).
CREATE TABLE applications (
    application_id    INT AUTO_INCREMENT PRIMARY KEY,                -- Unique ID, numbered automatically (Business rule #12)
    adopter_id        INT NOT NULL,                                  -- Who is applying (adopter) (Business rule #1)
    pet_id            INT NOT NULL,                                  -- Which pet an adopter wants (Business rule #3)
    employee_id       INT NOT NULL,                                  -- Staff member going over the application with an adopter (Business rule #20)
    application_date  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- Filled in with the current date and time (Business rule #12)
    status            ENUM('Pending', 'Approved', 'Rejected')
                      NOT NULL DEFAULT 'Pending',                    -- New applications start as Pending (Business rules #9 and #16)
    rejection_reason_id INT,                                         -- Why it was rejected, blank unless Rejected (Business rule #25)
    CONSTRAINT fk_applications_adopter
        FOREIGN KEY (adopter_id) REFERENCES adopters (adopter_id)
        ON DELETE RESTRICT,                                          -- Must match a real adopter
    CONSTRAINT fk_applications_pet
        FOREIGN KEY (pet_id) REFERENCES pets (pet_id)
        ON DELETE RESTRICT,                                          -- Must match a real pet, and blocks removing it (Business rules #17 and #28)
    CONSTRAINT fk_applications_employee
        FOREIGN KEY (employee_id) REFERENCES employees (employee_id)
        ON DELETE RESTRICT,                                          -- Must match a real employee
    CONSTRAINT fk_applications_reason
        FOREIGN KEY (rejection_reason_id) REFERENCES rejection_reasons (reason_id)
        ON DELETE RESTRICT,                                          -- Must match a real reason
    CONSTRAINT chk_applications_reason CHECK (
        (status = 'Rejected' AND rejection_reason_id IS NOT NULL)
        OR (status <> 'Rejected' AND rejection_reason_id IS NULL)
    )                                                                -- Rejected needs a reason, others have none (Business rule #25)
);

-- 7. VIEWS (the three application result tables: pending, approved and rejected)
-- A view is a saved SELECT. It looks and works like a table, but it holds no data of its own.
-- Each view reads the applications table, keeps one status, and shows names instead of ID numbers.
-- When staff approve or reject an application, its status changes, so it moves to the right view by itself (Business rule #16).
-- Without these views, every page would need its own long join just to show names instead of IDs.

CREATE OR REPLACE VIEW pending_applications AS                     -- Applications waiting for staff review.
SELECT a.application_id,                                           -- Which application.
       a.application_date,                                         -- When the adopter applied.
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,   -- Adopter's full name.
       ad.phone AS adopter_phone,                                  -- Phone, so staff can set up the home visit (Business rule #26).
       p.name AS pet_name,                                         -- Pet's name.
       p.species,                                                  -- Dog, Cat, Rabbit, Bird or Other.
       CONCAT(e.first_name, ' ', e.last_name) AS handled_by        -- Adoption employee in charge (Business rule #22).
FROM applications a                                                -- Start from the applications (a is a short name for the table).
INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id             -- Add the adopter who applied.
INNER JOIN pets p ON p.pet_id = a.pet_id                           -- Add the pet they want.
INNER JOIN employees e ON e.employee_id = a.employee_id            -- Add the employee handling it.
WHERE a.status = 'Pending'                                         -- Keep only Pending applications (Business rule #9).
ORDER BY a.application_id;                                         -- Oldest first.

CREATE OR REPLACE VIEW approved_applications AS                    -- Applications staff said yes to.
SELECT a.application_id,                                           -- Which application.
       a.application_date,                                         -- When the adopter applied.
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,   -- Who adopted the pet.
       p.name AS pet_name,                                         -- Which pet they adopted.
       p.species,                                                  -- Dog, Cat, Rabbit, Bird or Other.
       p.adoption_fee,                                             -- What the adoption cost.
       p.status AS pet_status,                                     -- Adopted, or Returned if the pet came back (Business rule #18).
       CONCAT(e.first_name, ' ', e.last_name) AS handled_by        -- Adoption employee who approved it (Business rule #22).
FROM applications a                                                -- Start from the applications.
INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id             -- Add the adopter.
INNER JOIN pets p ON p.pet_id = a.pet_id                           -- Add the pet.
INNER JOIN employees e ON e.employee_id = a.employee_id            -- Add the employee.
WHERE a.status = 'Approved'                                        -- Keep only Approved applications (Business rule #9).
ORDER BY a.application_id;                                         -- Oldest first.

CREATE OR REPLACE VIEW rejected_applications AS                         -- Applications staff said no to, with the reason.
SELECT a.application_id,                                                -- Which application.
       a.application_date,                                              -- When the adopter applied.
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,        -- Who applied.
       p.name AS pet_name,                                              -- Which pet they wanted.
       r.reason,                                                        -- Why it was rejected (Business rule #25).
       CONCAT('Business rule #', r.rule_number) AS rule_broken,         -- Which rule it broke.
       CONCAT(e.first_name, ' ', e.last_name) AS handled_by             -- Adoption employee who rejected it (Business rule #22).
FROM applications a                                                     -- Start from the applications.
INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id                  -- Add the adopter.
INNER JOIN pets p ON p.pet_id = a.pet_id                                -- Add the pet.
INNER JOIN employees e ON e.employee_id = a.employee_id                 -- Add the employee.
INNER JOIN rejection_reasons r ON r.reason_id = a.rejection_reason_id   -- Add the reason and its rule number.
WHERE a.status = 'Rejected'                                             -- Keep only Rejected applications (Business rule #9).
ORDER BY a.application_id;                                              -- Same order as seed.sql: by reason, then date.
