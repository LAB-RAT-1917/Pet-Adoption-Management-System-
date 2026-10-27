-- schema.sql
-- Project: Pet Adoption Management System (CIS 344 Group Project).
-- Purpose: Builds the pet_adoption database from scratch.
-- Table order: employees, pets, adopters, applications.

-- 1. RESET
-- Deletes an old database with the same name and makes a new, empty one.
DROP DATABASE IF EXISTS pet_adoption;
CREATE DATABASE pet_adoption;
USE pet_adoption;

-- 2. EMPLOYEE TABLE
-- One row per staff member.
-- Name and department are required (Business rule #23).
-- Department is Adoption or Sales (Business rule #24).
-- Only Adoption staff can handle applications (Business rule #22).
-- The app checks this, not the table.
-- Applications point here using employee_id.
CREATE TABLE employees (
    employee_id  INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(50) NOT NULL,
    last_name    VARCHAR(50) NOT NULL,
    department   ENUM('Adoption', 'Sales') NOT NULL
);

-- 3. PETS TABLE
-- One row per pet in the store.
-- The app handles status changes (Business rules #16, and #19).
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
-- Age 18+ is checked by the app when a customer apply (Business rule #14).
-- Applications point here using adopter_id.
CREATE TABLE adopters (
    adopter_id     INT AUTO_INCREMENT PRIMARY KEY,         -- Unique ID, numbered automatically (Business rule #12).
    first_name     VARCHAR(50) NOT NULL,                   -- Required (Business rule #6).
    last_name      VARCHAR(50) NOT NULL,                   -- Required (Business rule #6).
    phone          VARCHAR(20) NOT NULL,                   -- Stored as text, like (718) 555-0123
    email          VARCHAR(100) NOT NULL,                  -- Required, and no repeats (Business rules #6 and #7).
    address        VARCHAR(150) NOT NULL,                  -- Street, city, zip code, and state.
    date_of_birth  DATE NOT NULL,                          -- The app works out age from it (Business rule #14).
    date_joined    DATE NOT NULL DEFAULT (CURRENT_DATE),   -- Filled in with today's date (Business rule #12).
    CONSTRAINT uq_adopters_email UNIQUE (email)            -- No two adopters share an email (Business rule #7).
);

-- 5. APPLICATION TABLE 
-- One row per adoption application.
-- Links one adopter, one pet and one employee.
-- Foreign keys block deleting an adopter, pet or employee who has applications (Business rule #17).
-- The app checks eligibility before saving (Business rules #13, #14, #15, and #22).
-- Approve and reject run as a transaction (Business rule rule #16).
CREATE TABLE applications (
    application_id    INT AUTO_INCREMENT PRIMARY KEY,                -- Unique ID, numbered automatically (Business rule #12)
    adopter_id        INT NOT NULL,                                  -- Who is applying (adopter) (Business rule #1)
    pet_id            INT NOT NULL,                                  -- Which pet an adopter wants (Business rule #3)
    employee_id       INT NOT NULL,                                  -- Staff member going over the application with an adopter (Business rule #20)
    application_date  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- Filled in with the current date and time (Business rule #12)
    status            ENUM('Pending', 'Approved', 'Rejected')
                      NOT NULL DEFAULT 'Pending',                    -- New applications start as Pending (Business rules #9, and #16)
    CONSTRAINT fk_applications_adopter
        FOREIGN KEY (adopter_id) REFERENCES adopters (adopter_id)
        ON DELETE RESTRICT,                                          -- Must match a real adopter
    CONSTRAINT fk_applications_pet
        FOREIGN KEY (pet_id) REFERENCES pets (pet_id)
        ON DELETE RESTRICT,                                          -- Must match a real pet
    CONSTRAINT fk_applications_employee
        FOREIGN KEY (employee_id) REFERENCES employees (employee_id)
        ON DELETE RESTRICT                                           -- Must match a real employee
);
