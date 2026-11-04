-- seed.sql
-- Purpose: Fills the pet_adoption database with sample data.
-- Run schema.sql first, then this file. Safe to run again.
-- Insert order: employees, pets, adopters, applications.

USE pet_adoption;
SET NAMES utf8mb4;                                    -- Reads this file as UTF-8, so names like Beyoncé save correctly.

-- 1. CLEAR OLD DATA
-- Applications are deleted first, because they point to the other three tables (Business rule #17).
-- "<> 0" matches every adopter ID, even the negative ones. Safe Updates needs a WHERE on every DELETE.
DELETE FROM applications WHERE application_id > 0;
DELETE FROM adopters     WHERE adopter_id <> 0;
DELETE FROM pets         WHERE pet_id > 0;
DELETE FROM employees    WHERE employee_id > 0;

-- Restarts the IDs at 1, so the pets and applications get the same IDs on every run.
ALTER TABLE applications AUTO_INCREMENT = 1;
ALTER TABLE pets         AUTO_INCREMENT = 1;


-- 2. EMPLOYEES
-- The employees are named after 24 computing pioneers who paved the way in tech.
-- Who they are: famous computer scientists, mathematicians, and inventors, known for their good work.
-- What employee_id means: the year of each person's biggest breakthrough, like 1843 for Ada Lovelace's first computer program.
-- Every year is different, so every ID is unique (Business rule #12).
-- Without these employees, the applications would have no staff to point to.

-- Adoption department (15 employees). They handle applications (Business rule #22).
INSERT INTO employees (employee_id, first_name, last_name, department) VALUES
(1843, 'Ada',        'Lovelace',  'Adoption'),   -- English mathematician, the first computer programmer.
(1936, 'Alan',       'Turing',    'Adoption'),   -- English mathematician, the father of computer science.
(1946, 'Jean',       'Bartik',    'Adoption'),   -- One of the first programmers of the ENIAC computer.
(1947, 'Kathleen',   'Booth',     'Adoption'),   -- British computer scientist who invented assembly language.
(1948, 'Claude',     'Shannon',   'Adoption'),   -- American mathematician, the father of information theory.
(1949, 'Dorothy',    'Vaughan',   'Adoption'),   -- NASA mathematician and its first Black supervisor.
(1952, 'Grace',      'Hopper',    'Adoption'),   -- U.S. Navy admiral who built the first compiler.
(1955, 'Annie',      'Easley',    'Adoption'),   -- NASA programmer who wrote rocket software.
(1957, 'John',       'Backus',    'Adoption'),   -- IBM programmer who led the team that made FORTRAN.
(1958, 'Mary',       'Jackson',   'Adoption'),   -- NASA's first Black female engineer.
(1963, 'Charles',    'Bachman',   'Adoption'),   -- Built one of the first database systems.
(1970, 'Edgar',      'Codd',      'Adoption'),   -- IBM scientist who invented relational databases, like MySQL.
(1972, 'Dennis',     'Ritchie',   'Adoption'),   -- Created the C programming language.
(1976, 'Jerry',      'Lawson',    'Adoption'),   -- Engineer who invented the video game cartridge.
(1981, 'Jim',        'Gray',      'Adoption');   -- Database pioneer who defined transactions, like Business rule #16 uses.

-- Sales department (9 employees). They work the register and never handle applications (Business rule #22).
INSERT INTO employees (employee_id, first_name, last_name, department) VALUES
(1942, 'Hedy',       'Lamarr',    'Sales'),      -- Actress and inventor of frequency hopping, used in Wi-Fi.
(1962, 'Katherine',  'Johnson',   'Sales'),      -- NASA mathematician who checked John Glenn's orbit.
(1965, 'Gordon',     'Moore',     'Sales'),      -- Intel co-founder, known for Moore's Law.
(1966, 'Roy',        'Clay Sr.',  'Sales'),      -- Led the team that built HP's first computer.
(1968, 'Douglas',    'Engelbart', 'Sales'),      -- Inventor of the computer mouse.
(1969, 'Clarence',   'Ellis',     'Sales'),      -- First Black person to earn a PhD in computer science.
(1971, 'Ray',        'Tomlinson', 'Sales'),      -- Sent the first email and chose the @ sign for it.
(1978, 'Lynn',       'Conway',    'Sales'),      -- Pioneer of modern computer chip design.
(2006, 'Frances',    'Allen',     'Sales');      -- First woman to win the Turing Award, computing's top prize.


-- 3. PETS
-- 38 pets named after famous animals. MySQL numbers them 1 to 38 in the order listed (Business rule #12).
-- Within each kind, pets are listed Adopted, Returned, Pending, then Available, to match the applications in section 5.
-- Fees are the set prices for their age on the day they arrived (Business rules #29 to #32).

-- Dogs (IDs 1 to 17): Adopted 1 to 9, Pending 10 to 17
-- Only this INSERT lists estimated_age, because Cerberus is the one pet with no birth date (Business rule #31).
INSERT INTO pets (name, species, breed, birth_date, estimated_age, status, date_added, adoption_fee, vaccinated) VALUES
('Toto', 'Dog', 'Cairn Terrier', '2017-05-17', NULL, 'Adopted', '2020-05-30', 175.00, TRUE),
('Laika', 'Dog', 'Mixed', '2018-03-02', NULL, 'Adopted', '2020-08-22', 175.00, TRUE),
('Gromit', 'Dog', 'Beagle', '2019-02-08', NULL, 'Adopted', '2020-10-17', 175.00, TRUE),
('Balto', 'Dog', 'Siberian Husky', '2019-10-10', NULL, 'Adopted', '2021-03-27', 175.00, TRUE),
('Lassie', 'Dog', 'Rough Collie', '2019-04-12', NULL, 'Adopted', '2021-12-18', 175.00, TRUE),
('Clifford', 'Dog', 'Labrador mix', '2020-12-01', NULL, 'Adopted', '2022-07-09', 175.00, TRUE),
('Hachiko', 'Dog', 'Akita', '2021-11-10', NULL, 'Adopted', '2023-01-28', 175.00, TRUE),
('Pluto', 'Dog', 'Bloodhound', '2020-09-05', NULL, 'Adopted', '2023-04-22', 175.00, TRUE),
('Odie', 'Dog', 'Beagle mix', '2023-08-08', NULL, 'Adopted', '2025-02-15', 175.00, TRUE),
('Snoopy', 'Dog', 'Beagle', '2025-10-04', NULL, 'Pending', '2026-08-29', 250.00, TRUE),
('Bluey', 'Dog', 'Australian Cattle Dog', '2025-10-01', NULL, 'Pending', '2026-09-10', 250.00, TRUE),
('Bolt', 'Dog', 'White Shepherd', '2025-11-21', NULL, 'Pending', '2026-09-11', 250.00, TRUE),
('Marley', 'Dog', 'Labrador Retriever', '2024-12-25', NULL, 'Pending', '2026-09-17', 175.00, TRUE),
('Rin Tin Tin', 'Dog', 'German Shepherd', '2024-09-15', NULL, 'Pending', '2026-09-18', 175.00, TRUE),
('Cerberus', 'Dog', 'Rottweiler mix', NULL, 3, 'Pending', '2026-09-19', 175.00, TRUE),   -- No birth date, so staff estimated 3 years (Business rule #31).
('Beethoven', 'Dog', 'Saint Bernard', '2018-04-03', NULL, 'Pending', '2026-09-21', 100.00, TRUE),
('Scooby', 'Dog', 'Great Dane', '2025-09-13', NULL, 'Pending', '2026-09-29', 175.00, TRUE);

-- Cats (IDs 18 to 27): Adopted 18 to 23, Returned 24, Pending 25 and 26, Available 27
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Garfield', 'Cat', 'Orange Tabby', '2018-06-19', 'Adopted', '2020-02-20', 100.00, TRUE),
('Cheshire', 'Cat', 'British Shorthair', '2021-11-26', 'Adopted', '2022-10-01', 150.00, TRUE),
('Duchess', 'Cat', 'Turkish Angora', '2021-12-11', 'Adopted', '2023-08-05', 100.00, TRUE),
('Salem', 'Cat', 'Black Shorthair', '2014-02-14', 'Adopted', '2023-10-14', 50.00, TRUE),
('Figaro', 'Cat', 'Tuxedo', '2023-02-07', 'Adopted', '2024-08-24', 100.00, TRUE),
('Sylvester', 'Cat', 'Tuxedo', '2024-03-24', 'Adopted', '2025-07-26', 100.00, TRUE),
('Felix', 'Cat', NULL, '2019-11-09', 'Returned', '2021-01-16', 100.00, TRUE),   -- Breed unknown. Returned by its adopter (Business rule #18).
('Puss', 'Cat', 'Orange Tabby', '2025-05-05', 'Pending', '2026-09-15', 100.00, TRUE),
('Crookshanks', 'Cat', 'Persian', '2024-07-08', 'Pending', '2026-09-23', 100.00, TRUE),
('Tom', 'Cat', 'Russian Blue', '2024-02-10', 'Available', '2025-04-26', 100.00, TRUE);

-- Rabbits (IDs 28 to 30): Adopted 28, Pending 29, Available 30
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Peter', 'Rabbit', 'Netherland Dwarf', '2023-10-02', 'Adopted', '2024-03-30', 75.00, TRUE),
('Thumper', 'Rabbit', 'Lionhead', '2025-08-13', 'Pending', '2026-01-03', 75.00, TRUE),
('Bugs', 'Rabbit', 'Dutch', '2025-07-27', 'Available', '2026-06-20', 75.00, FALSE);   -- Not vaccinated, to test Business rule #13.

-- Birds (IDs 31 to 34): Adopted 31 and 32, Pending 33, Available 34
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Woodstock', 'Bird', 'Canary', '2020-06-22', 'Adopted', '2021-09-11', 60.00, TRUE),
('Iago', 'Bird', 'Scarlet Macaw', '2010-11-25', 'Adopted', '2022-03-19', 30.00, TRUE),
('Blu', 'Bird', 'Blue Macaw', '2024-04-15', 'Pending', '2026-09-16', 60.00, TRUE),
('Tweety', 'Bird', 'Canary', '2024-11-21', 'Available', '2025-05-24', 100.00, TRUE);

-- Other animals (IDs 35 to 38): Adopted 35, Pending 36, Available 37 and 38
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Nemo', 'Other', 'Clownfish', '2020-05-30', 'Adopted', '2021-07-03', 15.00, TRUE),
('Stuart', 'Other', 'Mouse', '2026-03-15', 'Pending', '2026-09-12', 10.00, TRUE),
('Dory', 'Other', 'Blue Tang', '2023-06-17', 'Available', '2024-05-18', 20.00, TRUE),
('Fenrir', 'Other', 'Ferret', '2025-01-15', 'Available', '2026-08-15', 55.00, FALSE);   -- Not vaccinated, to test Business rule #13.

-- 4. ADOPTERS (29 people in two parts: 16 historical figures and 13 musicians)
-- What adopter_id means: the store types in every ID.
-- Every column is filled in, because each one is required (Business rule #6).
-- No two adopters can share an email (Business rule #7).
-- Without these adopters, the applications would point to adopters that don't exist.

-- Part 1. Historical figures (IDs 1 to 16)
-- Who they are: Good people in history who spent their lives helping others.
-- adopter_id: 1 to 16, in the order they joined.
-- Email format: title.name@what-they-did.place (where they are from or where they did their work).
-- Phone numbers are made up.
-- date_joined is typed in here to build a history. In the app, MySQL fills in today's date (Business rule #12).
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
( 1, 'Bob', 'Ross', '(386) 555-0101', 'mr.bob.ross@happylittletrees.daytona', '12 Main St, Daytona Beach, FL 32114', '1942-10-29', '2020-02-08'),               -- Painter who taught millions to paint on TV.
( 2, 'Christa', 'McAuliffe', '(617) 555-0102', 'teacher.christa.mcauliffe@touchthefuture.boston', '2 Main St, Boston, MA 02108', '1948-09-02', '2020-05-16'),   -- Teacher chosen for NASA's Teacher in Space program.
( 3, 'Sally', 'Ride', '(213) 555-0103', 'dr.sally.ride@astronaut.losangeles', '26 Main St, Los Angeles, CA 90012', '1951-05-26', '2020-08-15'),                 -- Astronaut, the first American woman in space.
( 4, 'Jim', 'Henson', '(662) 555-0104', 'mr.jim.henson@muppets.greenville', '6 Main St, Greenville, MS 38701', '1936-09-24', '2020-10-10'),                     -- Puppeteer who created the Muppets.
( 5, 'Chadwick', 'Boseman', '(864) 555-0105', 'mr.chadwick.boseman@blackpanther.anderson', '29 Main St, Anderson, SC 29621', '1976-11-29', '2020-12-12'),       -- Actor who played Black Panther and visited sick children.
( 6, 'Paul', 'Farmer', '(413) 555-0106', 'dr.paul.farmer@globalhealth.haiti', '10 Main St, North Adams, MA 01247', '1959-10-26', '2021-01-09'),                 -- Doctor who brought health care to the poor in Haiti.
( 7, 'Roberto', 'Clemente', '(787) 555-0107', 'mr.roberto.clemente@humanitarian.puertorico', '21 Calle Sol, Carolina, PR 00983', '1934-08-18', '2021-03-20'),   -- Baseball Hall of Famer and humanitarian.
( 8, 'Steve', 'Irwin', '(929) 555-0108', 'mr.steve.irwin@crocodilehunter.australia', '9 Ocean Ave, Brooklyn, NY 11225', '1962-02-22', '2021-06-26'),            -- The Crocodile Hunter, who worked to protect wildlife.
( 9, 'Wangari', 'Maathai', '(718) 555-0109', 'dr.wangari.maathai@greenbelt.nyeri', '40 Green St, Bronx, NY 10451', '1940-04-01', '2021-09-04'),                 -- Nobel Peace Prize winner who planted millions of trees.
(10, 'Christopher', 'Reeve', '(212) 555-0110', 'mr.christopher.reeve@superman.newyork', '25 Main St, New York, NY 10019', '1952-09-25', '2021-12-11'),          -- Superman actor who pushed for spinal cord research.
(11, 'Chico', 'Mendes', '(347) 555-0111', 'mr.chico.mendes@rainforest.xapuri', '15 River Rd, Queens, NY 11354', '1944-12-15', '2022-03-12'),                    -- Rubber tapper who defended the Amazon rainforest.
(12, 'Selena', 'Quintanilla', '(979) 555-0112', 'ms.selena.quintanilla@tejanomusic.texas', '8 Main St, Lake Jackson, TX 77566', '1971-04-16', '2022-09-24'),    -- Singer known as the Queen of Tejano music.
(13, 'John', 'Lewis', '(334) 555-0113', 'congressman.john.lewis@goodtrouble.selma', '30 Main St, Troy, AL 36081', '1940-02-21', '2023-01-21'),                  -- Civil rights leader and congressman.
(14, 'Martin Luther', 'King Jr.', '(404) 555-0114', 'dr.martin.king@civilrights.atlanta', '50 Main St, Atlanta, GA 30303', '1929-01-15', '2023-04-15'),         -- Civil rights leader and Nobel Peace Prize winner.
(15, 'Arthur', 'Ashe', '(804) 555-0115', 'mr.arthur.ashe@tennischampion.richmond', '14 Main St, Richmond, VA 23219', '1943-07-10', '2024-05-11'),               -- Tennis champion and activist. No applications yet (LEFT JOIN test).
(16, 'Diana', 'Spencer', '(917) 555-0116', 'princess.diana.spencer@landminefree.sandringham', '1 Park Pl, New York, NY 10007', '1961-07-01', '2025-02-01');     -- Princess who campaigned against landmines. No applications yet (LEFT JOIN test).

-- Part 2. 13 Musicians, grouped by genre: pop, hip-hop, R&B, Latin, country, and rock
-- Who they are: famous singers, rappers, and one band.
-- adopter_id: their monthly Spotify listeners as of October 2026 (kworb.net), with a minus sign in front.
-- The minus sign keeps them apart from the store's regular IDs (1, 2, 3...). Typed in, so it never changes.
-- Phone and address: their record label's public office. Email format: name@recordlabel.genre.
-- date_joined: the day their first single came out (for Shakira and Dolly Parton, their first album).

-- Pop (7 music artists)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-110989116, 'Justin', 'Bieber', '(212) 333-8000', 'justin.bieber@defjamrecordings.pop', '1755 Broadway, New York, NY 10019', '1994-03-01', '2009-05-18'),         -- Canadian pop star who was discovered on YouTube.
(-101133786, 'Lady', 'Gaga', '(310) 865-1000', 'lady.gaga@interscoperecords.pop', '2220 Colorado Ave, Santa Monica, CA 90404', '1986-03-28', '2008-04-08'),        -- Pop star who won an Oscar for the song "Shallow".
(-92703326, 'Ariana', 'Grande', '(212) 333-8000', 'ariana.grande@republicrecords.pop', '1755 Broadway, New York, NY 10019', '1993-06-26', '2011-12-12'),           -- Pop singer who played Glinda in Wicked.
(-87266657, 'Michael', 'Jackson', '(212) 833-8000', 'michael.jackson@epicrecords.pop', '25 Madison Ave, New York, NY 10010', '1958-08-29', '1971-10-07'),          -- The King of Pop.
(-72023925, 'Olivia', 'Rodrigo', '(310) 865-1000', 'olivia.rodrigo@geffenrecords.pop', '2220 Colorado Ave, Santa Monica, CA 90404', '2003-02-20', '2021-01-08'),   -- Pop singer of drivers license. Applied once at 17 (Business rule #14 test).
(-48390856, 'Tate', 'McRae', '(212) 833-8000', 'tate.mcrae@rcarecords.pop', '25 Madison Ave, New York, NY 10010', '2003-07-01', '2017-12-28'),                     -- Canadian pop singer and dancer. Applied once at 17 (Business rule #14 test).
(-43861024, 'One', 'Direction', '(212) 833-4000', 'one.direction@columbiarecords.pop', '25 Madison Ave, New York, NY 10010', '2010-07-23', '2011-09-11');          -- Boy band formed on The X Factor. Under 18 as a band (Business rule #14 test).

-- Hip-hop (2 music artists)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-70633063, 'Kanye', 'West', '(212) 333-8000', 'kanye.west@defjamrecordings.hiphop', '1755 Broadway, New York, NY 10019', '1977-06-08', '2003-09-30'),   -- Rapper and producer.
(-47257785, 'Lil', 'Wayne', '(212) 333-8000', 'lil.wayne@republicrecords.hiphop', '1755 Broadway, New York, NY 10019', '1982-09-27', '1999-10-23');      -- Rapper who founded Young Money.

-- R&B (1 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-62962352, 'Beyoncé', 'Knowles-Carter', '(212) 833-4000', 'beyonce@columbiarecords.rnb', '25 Madison Ave, New York, NY 10010', '1981-09-04', '2002-06-11');   -- Singer who started out in Destiny's Child.

-- Latin (1 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-87947363, 'Shakira', 'Mebarak', '(305) 420-4900', 'shakira@sonymusiclatin.latin', '3390 Mary St, Suite 220, Coconut Grove, FL 33133', '1977-02-02', '1991-06-24');   -- Colombian singer known for the 2010 World Cup song "Waka Waka".

-- Country (1 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-23198483, 'Dolly', 'Parton', '(615) 324-7777', 'dolly.parton@bigmachinerecords.country', '1219 16th Ave S, Nashville, TN 37212', '1946-01-19', '1967-09-18');   -- Country legend who gave free books to children.

-- Rock (1 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-51308861, 'Elton', 'John', '(310) 865-1000', 'elton.john@interscoperecords.rock', '2220 Colorado Ave, Santa Monica, CA 90404', '1947-03-25', '1968-03-01');   -- Rock star who started the Elton John AIDS Foundation.

-- 5. APPLICATIONS
-- 51 applications are grouped by status. MySQL numbers them 1 to 51 in the order listed (Business rule #12).
-- Only Rejected applications have a reason ID from rejection_reasons (Business rule #25).

-- Approved (IDs 1 to 20). Staff said yes, so the pet became Adopted (Business rule #16).
INSERT INTO applications (adopter_id, pet_id, employee_id, application_date, status, rejection_reason_id) VALUES
(         1, 18, 1843, '2020-03-14 10:30:00', 'Approved', NULL),
(         2,  1, 1936, '2020-06-20 11:15:00', 'Approved', NULL),
(         3,  2, 1946, '2020-09-12 14:00:00', 'Approved', NULL),
(         4,  3, 1948, '2020-11-07 13:45:00', 'Approved', NULL),
(         6, 24, 1955, '2021-02-06 10:00:00', 'Approved', NULL),   -- Felix was returned later (Business rule #18).
(         7,  4, 1957, '2021-04-17 15:20:00', 'Approved', NULL),
(         8, 35, 1958, '2021-07-24 12:10:00', 'Approved', NULL),
(         9, 31, 1963, '2021-10-02 09:40:00', 'Approved', NULL),
(        10,  5, 1970, '2022-01-15 16:05:00', 'Approved', NULL),
(        11, 32, 1972, '2022-04-09 11:50:00', 'Approved', NULL),
(-101133786,  6, 1981, '2022-07-30 14:25:00', 'Approved', NULL),
(        12, 19, 1843, '2022-10-22 13:10:00', 'Approved', NULL),
(        13,  7, 1936, '2023-02-18 10:55:00', 'Approved', NULL),
( -51308861,  8, 1946, '2023-05-13 15:30:00', 'Approved', NULL),
( -62962352, 20, 1947, '2023-08-26 12:40:00', 'Approved', NULL),
(         5, 21, 1948, '2023-11-04 11:20:00', 'Approved', NULL),
( -23198483, 28, 1952, '2024-04-20 14:15:00', 'Approved', NULL),
(-110989116, 22, 1957, '2024-09-14 10:35:00', 'Approved', NULL),
( -92703326,  9, 1958, '2025-03-08 16:00:00', 'Approved', NULL),
( -87947363, 23, 1972, '2025-08-16 13:30:00', 'Approved', NULL);

-- Pending (IDs 21 to 33), waiting for staff review.
INSERT INTO applications (adopter_id, pet_id, employee_id, application_date, status, rejection_reason_id) VALUES
(         8, 11, 1947, '2026-10-01 10:15:00', 'Pending',  NULL),
( -70633063, 12, 1948, '2026-10-02 11:40:00', 'Pending',  NULL),
( -87266657, 36, 1949, '2026-10-03 14:05:00', 'Pending',  NULL),
(         6, 10, 1955, '2026-10-05 09:50:00', 'Pending',  NULL),
(         5, 25, 1957, '2026-10-06 15:35:00', 'Pending',  NULL),
(         9, 33, 1958, '2026-10-07 10:25:00', 'Pending',  NULL),
(        14, 13, 1970, '2026-10-08 13:15:00', 'Pending',  NULL),
( -48390856, 14, 1972, '2026-10-09 12:00:00', 'Pending',  NULL),
( -47257785, 15, 1976, '2026-10-10 16:20:00', 'Pending',  NULL),
( -92703326, 16, 1843, '2026-10-12 11:05:00', 'Pending',  NULL),
( -72023925, 26, 1936, '2026-10-14 14:45:00', 'Pending',  NULL),
(         2, 29, 1947, '2026-10-16 10:40:00', 'Pending',  NULL),
(         1, 17, 1949, '2026-10-20 15:10:00', 'Pending',  NULL);

-- Rejected (IDs 34 to 51), grouped by reason:
--   under 18: 34 to 36, not vaccinated: 37 to 39, already adopted: 40 to 42, pet has a pending application: 43 to 45,
--   pet returned: 46, adopter has a pending application: 47 and 48, home visit: 49 and 50, landlord: 51.
INSERT INTO applications (adopter_id, pet_id, employee_id, application_date, status, rejection_reason_id) VALUES
( -48390856,  3, 1947, '2020-10-24 11:00:00', 'Rejected', 1),
( -72023925, 24, 1952, '2021-01-23 13:30:00', 'Rejected', 1),
( -43861024, 34, 1970, '2025-06-14 15:00:00', 'Rejected', 1),
(        11, 30, 1843, '2026-07-11 10:05:00', 'Rejected', 2),
(         1, 30, 1936, '2026-08-22 16:15:00', 'Rejected', 2),
( -87947363, 38, 1946, '2026-09-05 13:40:00', 'Rejected', 2),
(         5, 18, 1949, '2021-01-09 12:30:00', 'Rejected', 3),
(         9,  1, 1976, '2022-05-28 14:50:00', 'Rejected', 3),
(        10,  7, 1949, '2024-01-13 11:10:00', 'Rejected', 3),
(         1, 11, 1952, '2026-10-04 14:35:00', 'Rejected', 4),
(         2, 10, 1981, '2026-10-11 15:55:00', 'Rejected', 4),
(        11, 12, 1946, '2026-10-15 10:30:00', 'Rejected', 4),
(        13, 24, 1981, '2026-06-06 14:20:00', 'Rejected', 5),
(         8, 13, 1963, '2026-10-07 12:50:00', 'Rejected', 6),
( -70633063, 27, 1948, '2026-10-18 13:05:00', 'Rejected', 6),
(         7, 37, 1955, '2024-06-08 13:20:00', 'Rejected', 7),
(         3, 27, 1963, '2025-05-17 10:45:00', 'Rejected', 7),
(        12, 29, 1976, '2026-01-24 12:15:00', 'Rejected', 8);

-- 6. QUICK CHECK
-- Each SELECT opens its own result tab. Compare it with the Expected line above it.

-- Rows in each table. Expected: employees 24, pets 38, adopters 29, applications 51, rejection_reasons 8.
SELECT 'employees' AS table_name, COUNT(*) AS row_count FROM employees
UNION ALL SELECT 'pets',              COUNT(*) FROM pets
UNION ALL SELECT 'adopters',          COUNT(*) FROM adopters
UNION ALL SELECT 'applications',      COUNT(*) FROM applications
UNION ALL SELECT 'rejection_reasons', COUNT(*) FROM rejection_reasons;

-- Applications by status. Expected: Approved 20, Pending 13, Rejected 18.
SELECT status, COUNT(*) AS how_many
FROM applications
GROUP BY status
ORDER BY FIELD(status, 'Approved', 'Pending', 'Rejected');

-- Pets by status. They must match the applications above (Business rule #16).
-- Expected: Adopted 19 and Returned 1 (the 20 Approved), Pending 13 (the 13 Pending), Available 5.
SELECT status, COUNT(*) AS how_many
FROM pets
GROUP BY status
ORDER BY FIELD(status, 'Adopted', 'Returned', 'Pending', 'Available');

-- Rejections by reason (Business rule #25). LEFT JOIN keeps a reason even if no application used it.
-- Expected how_many for reason IDs 1 to 8: 3, 3, 3, 3, 1, 2, 2, 1.
SELECT r.reason_id, r.reason, r.rule_number, COUNT(a.application_id) AS how_many
FROM rejection_reasons r
LEFT JOIN applications a ON a.rejection_reason_id = r.reason_id
GROUP BY r.reason_id, r.reason, r.rule_number
ORDER BY r.reason_id;

-- The three views. Expected: pending 13, approved 20, rejected 18.
SELECT (SELECT COUNT(*) FROM pending_applications)  AS pending,
       (SELECT COUNT(*) FROM approved_applications) AS approved,
       (SELECT COUNT(*) FROM rejected_applications) AS rejected;
