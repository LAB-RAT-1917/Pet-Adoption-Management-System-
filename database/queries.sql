-- queries.sql
-- Purpose: The join queries our pages use, written and tested in Workbench. 

USE pet_adoption;    -- Work in the pet_adoption database.
SET NAMES utf8mb4;   -- Show names like Beyoncé correctly.

-- 1. ALL APPLICATIONS WITH NAMES (INNER JOIN, for the Applications list page)
-- An application only stores ID numbers. Each JOIN swaps one ID for a name.
-- INNER JOIN keeps a row only when both sides match. Every application has an adopter, a pet and an employee (Business rules #1, #3 and #20).
-- Expected: 51 rows, oldest first, starting with Bob Ross and Garfield.
SELECT a.application_id,                                           -- Which application.
       a.application_date,                                         -- When the adopter applied.
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,   -- Swaps adopter_id for the adopter's name.
       p.name AS pet_name,                                         -- Swaps pet_id for the pet's name.
       CONCAT(e.first_name, ' ', e.last_name) AS handled_by,       -- Swaps employee_id for the employee's name.
       a.status                                                    -- Pending, Approved or Rejected.
FROM applications a                                                -- Start from the applications (a is a short name for the table).
INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id             -- Match each application to its adopter.
INNER JOIN pets p ON p.pet_id = a.pet_id                           -- Match it to its pet.
INNER JOIN employees e ON e.employee_id = a.employee_id            -- Match it to the employee who handled it.
ORDER BY a.application_date;                                       -- Oldest first.

-- 2. EVERY ADOPTER AND HOW MANY APPLICATIONS THEY HAVE (LEFT JOIN, for the Adopters list page)
-- LEFT JOIN keeps every adopter, even one with no applications. An INNER JOIN would drop them.
-- An adopter can have many applications, or none (Business rule #2).
-- Expected: 29 rows. Arthur Ashe and Diana Spencer show 0, so they are at the top.
SELECT ad.adopter_id,                                              -- Which adopter.
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,   -- Adopter's full name.
       ad.phone,                                                   -- The only contact detail lists show.
       COUNT(a.application_id) AS applications                     -- Counts only real matches, so no applications shows 0.
FROM adopters ad                                                   -- Start from the adopters, so all 29 are kept.
LEFT JOIN applications a ON a.adopter_id = ad.adopter_id           -- Add their applications, if they have any.
GROUP BY ad.adopter_id, ad.first_name, ad.last_name, ad.phone      -- One row per adopter.
ORDER BY applications, adopter_name;                               -- Fewest applications first, then by name.

-- 3. EVERY EMPLOYEE AND HOW MANY APPLICATIONS THEY HANDLED (LEFT JOIN)
-- LEFT JOIN keeps every employee, even one who handled none (Business rule #21).
-- Only Adoption staff handle applications, so every Sales employee shows 0 (Business rule #22).
-- Expected: 24 rows. The 15 Adoption staff show 3 or 4, and the 9 Sales staff show 0.
SELECT e.employee_id,                                             -- Badge number.
       CONCAT(e.first_name, ' ', e.last_name) AS employee_name,   -- Employee's full name.
       e.department,                                              -- Adoption or Sales.
       COUNT(a.application_id) AS applications_handled            -- Counts only real matches, so none handled shows 0.
FROM employees e                                                  -- Start from the employees, so all 24 are kept.
LEFT JOIN applications a ON a.employee_id = e.employee_id         -- Add the applications they handled, if any.
GROUP BY e.employee_id, e.first_name, e.last_name, e.department   -- One row per employee.
ORDER BY e.department, e.employee_id;                             -- Adoption first, then Sales, in badge order.
