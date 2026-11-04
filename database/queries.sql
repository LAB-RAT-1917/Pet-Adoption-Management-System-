-- queries.sql
-- Purpose: Three join queries (one INNER JOIN and two LEFT JOINs), tested in Workbench.

USE pet_adoption;
SET NAMES utf8mb4;   -- Shows names like Beyoncé correctly.

-- 1. ALL APPLICATIONS WITH NAMES (INNER JOIN)
-- An application only stores ID numbers. Each JOIN swaps one ID for a name. a, ad, p and e are short names for the tables.
-- INNER JOIN keeps a row only when both sides match. Every application has an adopter, a pet and an employee (Business rules #1, #3 and #20).
-- Expected: 51 rows, oldest first, starting with Bob Ross and Garfield.
SELECT a.application_id,
       a.application_date,
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,
       p.name AS pet_name,
       CONCAT(e.first_name, ' ', e.last_name) AS handled_by,
       a.status
FROM applications a
INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id
INNER JOIN pets p ON p.pet_id = a.pet_id
INNER JOIN employees e ON e.employee_id = a.employee_id
ORDER BY a.application_date;

-- 2. EVERY ADOPTER AND HOW MANY APPLICATIONS THEY HAVE (LEFT JOIN)
-- LEFT JOIN keeps every adopter, even one with no applications. An INNER JOIN would drop them (Business rule #2).
-- COUNT(a.application_id) counts only real matches, so an adopter with no applications shows 0.
-- Expected: 29 rows. Arthur Ashe and Diana Spencer show 0, so they are at the top.
SELECT ad.adopter_id,
       CONCAT(ad.first_name, ' ', ad.last_name) AS adopter_name,
       ad.phone,
       COUNT(a.application_id) AS applications
FROM adopters ad
LEFT JOIN applications a ON a.adopter_id = ad.adopter_id
GROUP BY ad.adopter_id, ad.first_name, ad.last_name, ad.phone
ORDER BY applications, adopter_name;

-- 3. EVERY EMPLOYEE AND HOW MANY APPLICATIONS THEY HANDLED (LEFT JOIN)
-- LEFT JOIN keeps every employee, even one who handled none (Business rule #21).
-- Only Adoption staff handle applications, so every Sales employee shows 0 (Business rule #22).
-- Expected: 24 rows. The 15 Adoption staff show 3 or 4, and the 9 Sales staff show 0.
SELECT e.employee_id,
       CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
       e.department,
       COUNT(a.application_id) AS applications_handled
FROM employees e
LEFT JOIN applications a ON a.employee_id = e.employee_id
GROUP BY e.employee_id, e.first_name, e.last_name, e.department
ORDER BY e.department, e.employee_id;
