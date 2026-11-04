USE pet_adoption;                                     -- Pulls the pet_adoption database.

-- 1. CLEAR OLD DATA
-- Empties the tables so this file can run multiple times.
DELETE FROM applications WHERE application_id > 0;   -- Delete all applications first, because they point to the other three tables (Business rule #17).
DELETE FROM adopters     WHERE adopter_id > 0;       -- Delete all adopters. "> 0" matches every ID, and Safe Updates needs a WHERE to run a DELETE.
DELETE FROM pets         WHERE pet_id > 0;           -- Delete all pets, so running this file again won't add them twice.
DELETE FROM employees    WHERE employee_id > 0;      -- Delete all employees last, because nothing points to them anymore.
-- Without these deletes, a second run would add every row again and stop at the repeated emails.

ALTER TABLE applications AUTO_INCREMENT = 1;         -- Restart application IDs at 1, so they match the rows below.
ALTER TABLE adopters     AUTO_INCREMENT = 1;         -- Restart adopter IDs at 1.
ALTER TABLE pets         AUTO_INCREMENT = 1;         -- Restart pet IDs at 1.
ALTER TABLE employees    AUTO_INCREMENT = 1;         -- Restart employee IDs at 1.
-- Without these ALTER TABLEs, new IDs would start where the old ones stopped (pets at 39).
-- So the applications would point to IDs that don't exist.


-- 2. EMPLOYEES 
-- The employees are named after 24 computing pioneers who paved the way in tech.
-- Who they are: famous computer scientists, mathematicians, and inventors, known for their good work.
-- What employee_id means: the year of each person's biggest breakthrough, like 1843 for Ada Lovelace's first computer program.
-- Every year is different, so every ID is unique (Business rule #12).
-- Without these employees, the applications would have no staff to point to

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


-- 3. PETS (38 pets, grouped by kind: dogs, cats, rabbits, birds, then other animals).
-- Each pet is named after a famous animal from cartoons, books, movies, myths, or real life.
-- What pet_id means: MySQL numbers the pets 1 to 38 in the order they are listed here (Business rule #12).
-- Within each kind, pets are listed in the order they arrived at the store.
-- Each status matches the applications in section 5 (Business rules #8 and #16).
-- Without these pets, the applications would point to pets that don't exist.

-- Dogs (17 dogs, IDs 1 to 17): Adopted 1 to 9, Pending 10 to 17
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Toto', 'Dog', 'Cairn Terrier', '2017-05-17', 'Adopted', '2020-05-30', 150.00, TRUE),            -- 1: Dorothy's dog in The Wizard of Oz.
('Laika', 'Dog', 'Mixed', '2018-03-02', 'Adopted', '2020-08-22', 120.00, TRUE),                   -- 2: Real dog, the first animal to orbit Earth.
('Gromit', 'Dog', 'Beagle', '2019-02-08', 'Adopted', '2020-10-17', 140.00, TRUE),                 -- 3: Wallace's clever dog in Wallace & Gromit.
('Balto', 'Dog', 'Siberian Husky', '2019-10-10', 'Adopted', '2021-03-27', 175.00, TRUE),          -- 4: Real sled dog who helped bring medicine to Nome, Alaska.
('Lassie', 'Dog', 'Rough Collie', '2019-04-12', 'Adopted', '2021-12-18', 200.00, TRUE),           -- 5: The loyal collie from books, movies, and TV.
('Clifford', 'Dog', 'Labrador mix', '2020-12-01', 'Adopted', '2022-07-09', 160.00, TRUE),         -- 6: The Big Red Dog from the children's books.
('Hachiko', 'Dog', 'Akita', '2021-11-10', 'Adopted', '2023-01-28', 225.00, TRUE),                 -- 7: Real Akita in Japan, famous for his loyalty.
('Pluto', 'Dog', 'Bloodhound', '2020-09-05', 'Adopted', '2023-04-22', 130.00, TRUE),              -- 8: Mickey Mouse's dog.
('Odie', 'Dog', 'Beagle mix', '2023-08-08', 'Adopted', '2025-02-15', 110.00, TRUE),               -- 9: Garfield's dog friend in the comic strip.
('Snoopy', 'Dog', 'Beagle', '2025-10-04', 'Pending', '2026-08-29', 150.00, TRUE),                 -- 10: Charlie Brown's beagle in Peanuts.
('Bluey', 'Dog', 'Australian Cattle Dog', '2025-10-01', 'Pending', '2026-09-10', 175.00, TRUE),   -- 11: The puppy from the cartoon Bluey.
('Bolt', 'Dog', 'White Shepherd', '2025-11-21', 'Pending', '2026-09-11', 190.00, TRUE),           -- 12: The superhero dog from the movie Bolt.
('Marley', 'Dog', 'Labrador Retriever', '2024-12-25', 'Pending', '2026-09-17', 165.00, TRUE),     -- 13: The lovable Labrador from Marley & Me.
('Rin Tin Tin', 'Dog', 'German Shepherd', '2024-09-15', 'Pending', '2026-09-18', 210.00, TRUE),   -- 14: Real German Shepherd who starred in early movies.
('Cerberus', 'Dog', 'Rottweiler mix', NULL, 'Pending', '2026-09-19', 140.00, TRUE),               -- 15: Three-headed guard dog from Greek myth. Birth date unknown.
('Beethoven', 'Dog', 'Saint Bernard', '2023-04-03', 'Pending', '2026-09-21', 185.00, TRUE),       -- 16: The giant Saint Bernard from the movie Beethoven.
('Scooby', 'Dog', 'Great Dane', '2025-09-13', 'Pending', '2026-09-29', 200.00, TRUE);             -- 17: Scooby-Doo, the mystery-solving Great Dane.

-- Cats (10 cats, IDs 18 to 27): Adopted 18 to 23, Returned 24, Pending 25 and 26, Available 27
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Garfield', 'Cat', 'Orange Tabby', '2018-06-19', 'Adopted', '2020-02-20', 75.00, TRUE),         -- 18: The cat from the comic strip.
('Cheshire', 'Cat', 'British Shorthair', '2021-11-26', 'Adopted', '2022-10-01', 110.00, TRUE),   -- 19: The grinning cat from Alice in Wonderland.
('Duchess', 'Cat', 'Turkish Angora', '2021-12-11', 'Adopted', '2023-08-05', 120.00, TRUE),       -- 20: The elegant mother cat from The Aristocats.
('Salem', 'Cat', 'Black Shorthair', '2022-02-14', 'Adopted', '2023-10-14', 65.00, TRUE),         -- 21: The talking black cat from Sabrina the Teenage Witch.
('Figaro', 'Cat', 'Tuxedo', '2023-02-07', 'Adopted', '2024-08-24', 90.00, TRUE),                 -- 22: Geppetto's kitten in Pinocchio.
('Sylvester', 'Cat', 'Tuxedo', '2024-03-24', 'Adopted', '2025-07-26', 85.00, TRUE),              -- 23: The cat who chases Tweety in Looney Tunes.
('Felix', 'Cat', NULL, '2019-11-09', 'Returned', '2021-01-16', 60.00, TRUE),                     -- 24: Felix the Cat, an early cartoon star. Breed unknown. Returned (Business rule #18).
('Puss', 'Cat', 'Orange Tabby', '2025-05-05', 'Pending', '2026-09-15', 80.00, TRUE),             -- 25: Puss in Boots, the clever cat from the fairy tale.
('Crookshanks', 'Cat', 'Persian', '2024-07-08', 'Pending', '2026-09-23', 125.00, TRUE),          -- 26: Hermione's cat in Harry Potter.
('Tom', 'Cat', 'Russian Blue', '2024-02-10', 'Available', '2025-04-26', 95.00, TRUE);            -- 27: The cat who chases Jerry in Tom and Jerry.

-- Rabbits (3 rabbits, IDs 28 to 30): Adopted 28, Pending 29, Available 30
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Peter', 'Rabbit', 'Netherland Dwarf', '2023-10-02', 'Adopted', '2024-03-30', 45.00, TRUE),   -- 28: Peter Rabbit from Beatrix Potter's books.
('Thumper', 'Rabbit', 'Lionhead', '2025-08-13', 'Pending', '2026-01-03', 50.00, TRUE),         -- 29: Bambi's rabbit friend.
('Bugs', 'Rabbit', 'Dutch', '2025-07-27', 'Available', '2026-06-20', 40.00, FALSE);            -- 30: Bugs Bunny from Looney Tunes. Not vaccinated (Business rule #13 test).

-- Birds (4 birds, IDs 31 to 34): Adopted 31 and 32, Pending 33, Available 34
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Woodstock', 'Bird', 'Canary', '2020-06-22', 'Adopted', '2021-09-11', 35.00, TRUE),     -- 31: Snoopy's little bird friend in Peanuts.
('Iago', 'Bird', 'Scarlet Macaw', '2018-11-25', 'Adopted', '2022-03-19', 80.00, TRUE),   -- 32: The talking parrot from Aladdin.
('Blu', 'Bird', 'Blue Macaw', '2024-04-15', 'Pending', '2026-09-16', 95.00, TRUE),       -- 33: The blue macaw from the movie Rio.
('Tweety', 'Bird', 'Canary', '2024-11-21', 'Available', '2025-05-24', 30.00, TRUE);      -- 34: The little yellow bird from Looney Tunes.

-- Other animals (4 other animals, IDs 35 to 38): Adopted 35, Pending 36, Available 37 and 38
INSERT INTO pets (name, species, breed, birth_date, status, date_added, adoption_fee, vaccinated) VALUES
('Nemo', 'Other', 'Clownfish', '2020-05-30', 'Adopted', '2021-07-03', 15.00, TRUE),     -- 35: The clownfish from Finding Nemo.
('Stuart', 'Other', 'Mouse', '2026-03-15', 'Pending', '2026-09-12', 10.00, TRUE),       -- 36: Stuart Little, the mouse from the children's book.
('Dory', 'Other', 'Blue Tang', '2023-06-17', 'Available', '2024-05-18', 20.00, TRUE),   -- 37: The forgetful blue tang from Finding Nemo.
('Fenrir', 'Other', 'Ferret', '2025-01-15', 'Available', '2026-08-15', 55.00, FALSE);   -- 38: Named after the giant wolf in Norse myth. Not vaccinated (Business rule #13 test).

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
-- date_joined: the day their first single came out.

-- Pop (7 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-110989116, 'Justin', 'Bieber', '(212) 333-8000', 'justin.bieber@defjamrecordings.pop', '1755 Broadway, New York, NY 10019', '1994-03-01', '2009-05-18'),         -- Canadian pop star who was discovered on YouTube.
(-101133786, 'Lady', 'Gaga', '(310) 865-1000', 'lady.gaga@interscoperecords.pop', '2220 Colorado Ave, Santa Monica, CA 90404', '1986-03-28', '2008-04-08'),        -- Pop star who won an Oscar for the song "Shallow".
(-92703326, 'Ariana', 'Grande', '(212) 333-8000', 'ariana.grande@republicrecords.pop', '1755 Broadway, New York, NY 10019', '1993-06-26', '2011-12-12'),           -- Pop singer who played Glinda in Wicked.
(-87266657, 'Michael', 'Jackson', '(212) 833-8000', 'michael.jackson@epicrecords.pop', '25 Madison Ave, New York, NY 10010', '1958-08-29', '1971-10-07'),          -- The King of Pop.
(-72023925, 'Olivia', 'Rodrigo', '(310) 865-1000', 'olivia.rodrigo@geffenrecords.pop', '2220 Colorado Ave, Santa Monica, CA 90404', '2003-02-20', '2021-01-08'),   -- Pop singer of drivers license. Applied once at 17 (Business rule #14 test).
(-48390856, 'Tate', 'McRae', '(212) 833-8000', 'tate.mcrae@rcarecords.pop', '25 Madison Ave, New York, NY 10010', '2003-07-01', '2017-12-28'),                     -- Canadian pop singer and dancer. Applied once at 17 (Business rule #14 test).
(-43861024, 'One', 'Direction', '(212) 833-4000', 'one.direction@columbiarecords.pop', '25 Madison Ave, New York, NY 10010', '2010-07-23', '2011-09-11');          -- Boy band formed on The X Factor. Under 18 as a band (Business rule #14 test).

-- Hip-hop (2 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-70633063, 'Kanye', 'West', '(212) 333-8000', 'kanye.west@defjamrecordings.hiphop', '1755 Broadway, New York, NY 10019', '1977-06-08', '2003-09-30'),   -- Rapper and producer. 
(-47257785, 'Lil', 'Wayne', '(212) 333-8000', 'lil.wayne@republicrecords.hiphop', '1755 Broadway, New York, NY 10019', '1982-09-27', '1999-10-23');      -- Rapper who founded Young Money.

-- R&B (1 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-62962352, 'Beyoncé', 'Knowles-Carter', '(212) 833-4000', 'beyonce@columbiarecords.rnb', '25 Madison Ave, New York, NY 10010', '1981-09-04', '2002-06-11');   -- Singer who started out in Destiny's Child.

-- Latin (1 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-87947363, 'Shakira', 'Mebarak', '(305) 420-4900', 'shakira@sonymusiclatin.latin', '3390 Mary St, Suite 220, Coconut Grove, FL 33133', '1977-02-02', '1991-06-24');   -- Colombian singer known for the 2010 World Cup song "waka-waka". 

-- Country (1 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-23198483, 'Dolly', 'Parton', '(615) 324-7777', 'dolly.parton@bigmachinerecords.country', '1219 16th Ave S, Nashville, TN 37212', '1946-01-19', '1967-09-18');   -- Country legend who gave free books to children. 

-- Rock (1 music artist)
INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined) VALUES
(-51308861, 'Elton', 'John', '(310) 865-1000', 'elton.john@interscoperecords.rock', '2220 Colorado Ave, Santa Monica, CA 90404', '1947-03-25', '1968-03-01');   -- Rock star who started the Elton John AIDS Foundation.

SELECT adopter_id, first_name, last_name FROM adopters ORDER BY adopter_id;   -- List every adopter by ID.

-- 5. APPLICATIONS (51 applications, grouped by status: Approved, Pending, Rejected)
-- What application_id means: MySQL numbers them 1 to 51 in the order they are listed here (Business rule #12).
-- Each application links an adopter, a pet and the Adoption employee who handled it (Business rules #1, #3 and #20).
-- Every Rejected application has a reason from rejection_reasons. The others have NULL (Business rule #25).
-- Without these applications, every join between adopters, pets and employees would come back empty.

-- Approved (20, IDs 1 to 20): staff said yes, so the pet became Adopted (Business rule #16).
INSERT INTO applications (adopter_id, pet_id, employee_id, application_date, status, rejection_reason_id) VALUES
(         1, 18, 1843, '2020-03-14 10:30:00', 'Approved', NULL),   -- 1: Bob Ross adopted Garfield.
(         2,  1, 1936, '2020-06-20 11:15:00', 'Approved', NULL),   -- 2: Christa McAuliffe adopted Toto.
(         3,  2, 1946, '2020-09-12 14:00:00', 'Approved', NULL),   -- 3: Sally Ride adopted Laika.
(         4,  3, 1948, '2020-11-07 13:45:00', 'Approved', NULL),   -- 4: Jim Henson adopted Gromit.
(         6, 24, 1955, '2021-02-06 10:00:00', 'Approved', NULL),   -- 5: Paul Farmer adopted Felix. Returned later (Business rule #18).
(         7,  4, 1957, '2021-04-17 15:20:00', 'Approved', NULL),   -- 6: Roberto Clemente adopted Balto.
(         8, 35, 1958, '2021-07-24 12:10:00', 'Approved', NULL),   -- 7: Steve Irwin adopted Nemo.
(         9, 31, 1963, '2021-10-02 09:40:00', 'Approved', NULL),   -- 8: Wangari Maathai adopted Woodstock.
(        10,  5, 1970, '2022-01-15 16:05:00', 'Approved', NULL),   -- 9: Christopher Reeve adopted Lassie.
(        11, 32, 1972, '2022-04-09 11:50:00', 'Approved', NULL),   -- 10: Chico Mendes adopted Iago.
(-101133786,  6, 1981, '2022-07-30 14:25:00', 'Approved', NULL),   -- 11: Lady Gaga adopted Clifford.
(        12, 19, 1843, '2022-10-22 13:10:00', 'Approved', NULL),   -- 12: Selena Quintanilla adopted Cheshire.
(        13,  7, 1936, '2023-02-18 10:55:00', 'Approved', NULL),   -- 13: John Lewis adopted Hachiko.
( -51308861,  8, 1946, '2023-05-13 15:30:00', 'Approved', NULL),   -- 14: Elton John adopted Pluto.
( -62962352, 20, 1947, '2023-08-26 12:40:00', 'Approved', NULL),   -- 15: Beyoncé adopted Duchess.
(         5, 21, 1948, '2023-11-04 11:20:00', 'Approved', NULL),   -- 16: Chadwick Boseman adopted Salem.
( -23198483, 28, 1952, '2024-04-20 14:15:00', 'Approved', NULL),   -- 17: Dolly Parton adopted Peter.
(-110989116, 22, 1957, '2024-09-14 10:35:00', 'Approved', NULL),   -- 18: Justin Bieber adopted Figaro.
( -92703326,  9, 1958, '2025-03-08 16:00:00', 'Approved', NULL),   -- 19: Ariana Grande adopted Odie.
( -87947363, 23, 1972, '2025-08-16 13:30:00', 'Approved', NULL);   -- 20: Shakira adopted Sylvester.

-- Pending (13, IDs 21 to 33): waiting for staff review. 
INSERT INTO applications (adopter_id, pet_id, employee_id, application_date, status, rejection_reason_id) VALUES
(         8, 11, 1947, '2026-10-01 10:15:00', 'Pending',  NULL),   -- 21: Steve Irwin wants Bluey.
( -70633063, 12, 1948, '2026-10-02 11:40:00', 'Pending',  NULL),   -- 22: Kanye West wants Bolt.
( -87266657, 36, 1949, '2026-10-03 14:05:00', 'Pending',  NULL),   -- 23: Michael Jackson wants Stuart.
(         6, 10, 1955, '2026-10-05 09:50:00', 'Pending',  NULL),   -- 24: Paul Farmer wants Snoopy.
(         5, 25, 1957, '2026-10-06 15:35:00', 'Pending',  NULL),   -- 25: Chadwick Boseman wants Puss.
(         9, 33, 1958, '2026-10-07 10:25:00', 'Pending',  NULL),   -- 26: Wangari Maathai wants Blu.
(        14, 13, 1970, '2026-10-08 13:15:00', 'Pending',  NULL),   -- 27: Martin Luther King Jr. wants Marley.
( -48390856, 14, 1972, '2026-10-09 12:00:00', 'Pending',  NULL),   -- 28: Tate McRae wants Rin Tin Tin.
( -47257785, 15, 1976, '2026-10-10 16:20:00', 'Pending',  NULL),   -- 29: Lil Wayne wants Cerberus.
( -92703326, 16, 1843, '2026-10-12 11:05:00', 'Pending',  NULL),   -- 30: Ariana Grande wants Beethoven.
( -72023925, 26, 1936, '2026-10-14 14:45:00', 'Pending',  NULL),   -- 31: Olivia Rodrigo wants Crookshanks.
(         2, 29, 1947, '2026-10-16 10:40:00', 'Pending',  NULL),   -- 32: Christa McAuliffe wants Thumper.
(         1, 17, 1949, '2026-10-20 15:10:00', 'Pending',  NULL);   -- 33: Bob Ross wants Scooby.

-- Rejected (18, IDs 34 to 51), grouped by reason:
--   under 18: 34 to 36, not vaccinated: 37 to 39, already adopted: 40 to 42, pet has a pending application: 43 to 45,
--   pet returned: 46, adopter has a pending application: 47 and 48, home visit: 49 and 50, landlord: 51.
INSERT INTO applications (adopter_id, pet_id, employee_id, application_date, status, rejection_reason_id) VALUES
( -48390856,  3, 1947, '2020-10-24 11:00:00', 'Rejected', 1),   -- 34: Tate McRae for Gromit at 17. Adopter is under 18 (Business rule #14).
( -72023925, 24, 1952, '2021-01-23 13:30:00', 'Rejected', 1),   -- 35: Olivia Rodrigo for Felix at 17. Adopter is under 18 (Business rule #14).
( -43861024, 34, 1970, '2025-06-14 15:00:00', 'Rejected', 1),   -- 36: One Direction for Tweety at 14. Adopter is under 18 (Business rule #14).
(        11, 30, 1843, '2026-07-11 10:05:00', 'Rejected', 2),   -- 37: Chico Mendes for Bugs. Pet is not vaccinated (Business rule #13).
(         1, 30, 1936, '2026-08-22 16:15:00', 'Rejected', 2),   -- 38: Bob Ross for Bugs. Pet is not vaccinated (Business rule #13).
( -87947363, 38, 1946, '2026-09-05 13:40:00', 'Rejected', 2),   -- 39: Shakira for Fenrir. Pet is not vaccinated (Business rule #13).
(         5, 18, 1949, '2021-01-09 12:30:00', 'Rejected', 3),   -- 40: Chadwick Boseman for Garfield. Pet is already adopted (Business rule #13).
(         9,  1, 1976, '2022-05-28 14:50:00', 'Rejected', 3),   -- 41: Wangari Maathai for Toto. Pet is already adopted (Business rule #13).
(        10,  7, 1949, '2024-01-13 11:10:00', 'Rejected', 3),   -- 42: Christopher Reeve for Hachiko. Pet is already adopted (Business rule #13).
(         1, 11, 1952, '2026-10-04 14:35:00', 'Rejected', 4),   -- 43: Bob Ross for Bluey. Pet already has a pending application (Business rule #13).
(         2, 10, 1981, '2026-10-11 15:55:00', 'Rejected', 4),   -- 44: Christa McAuliffe for Snoopy. Pet already has a pending application (Business rule #13).
(        11, 12, 1946, '2026-10-15 10:30:00', 'Rejected', 4),   -- 45: Chico Mendes for Bolt. Pet already has a pending application (Business rule #13).
(        13, 24, 1981, '2026-06-06 14:20:00', 'Rejected', 5),   -- 46: John Lewis for Felix. Pet was returned and is still in care (Business rule #13).
(         8, 13, 1963, '2026-10-07 12:50:00', 'Rejected', 6),   -- 47: Steve Irwin for Marley. Adopter already has a pending application (Business rule #15).
( -70633063, 27, 1948, '2026-10-18 13:05:00', 'Rejected', 6),   -- 48: Kanye West for Tom. Adopter already has a pending application (Business rule #15).
(         7, 37, 1955, '2024-06-08 13:20:00', 'Rejected', 7),   -- 49: Roberto Clemente for Dory. Did not pass the home visit (Business rule #26).
(         3, 27, 1963, '2025-05-17 10:45:00', 'Rejected', 7),   -- 50: Sally Ride for Tom. Did not pass the home visit (Business rule #26).
(        12, 29, 1976, '2026-01-24 12:15:00', 'Rejected', 8);   -- 51: Selena Quintanilla for Thumper. Landlord does not allow pets (Business rule #27).

-- 6. QUICK CHECK (four SELECTs that show everything loaded the right way)
-- Each SELECT opens its own result tab. Compare each one with the "Expected" line above it.
-- Without these checks, a missing or doubled row could go unnoticed until the app shows the wrong data.

-- How many rows each table has.
-- Expected: employees 24, pets 38, adopters 29, applications 51, rejection_reasons 8.
SELECT 'employees' AS table_name, COUNT(*) AS row_count FROM employees   -- Count the employees.
UNION ALL SELECT 'pets',              COUNT(*) FROM pets                 -- Add the pet count as the next row.
UNION ALL SELECT 'adopters',          COUNT(*) FROM adopters             -- Add the adopter count.
UNION ALL SELECT 'applications',      COUNT(*) FROM applications         -- Add the application count.
UNION ALL SELECT 'rejection_reasons', COUNT(*) FROM rejection_reasons;   -- Add the reason count (schema.sql adds these 8).

-- How many applications have each status.
-- Expected: Approved 20, Pending 13, Rejected 18.
SELECT status, COUNT(*) AS how_many                          -- Show each status and how many applications have it.
FROM applications                                            -- Look in the applications table.
GROUP BY status                                              -- Put applications with the same status together.
ORDER BY FIELD(status, 'Approved', 'Pending', 'Rejected');   -- List them in the same order as section 5.

-- How many pets have each status. They should match the applications above (Business rule #16).
-- Expected: Adopted 19 and Returned 1 (the 20 Approved), Pending 13 (the 13 Pending), Available 5.
SELECT status, COUNT(*) AS how_many                                      -- Show each pet status and how many pets have it.
FROM pets                                                                -- Look in the pets table.
GROUP BY status                                                          -- Put pets with the same status together.
ORDER BY FIELD(status, 'Adopted', 'Returned', 'Pending', 'Available');   -- List them in the same order as section 3.

-- Why the 18 applications were rejected, and the rule each one broke (Business rule #25).
-- Expected how_many for reason IDs 1 to 8: 3, 3, 3, 3, 1, 2, 2, 1.
SELECT r.reason_id, r.reason, r.rule_number, COUNT(a.application_id) AS how_many   -- Show each reason, its rule, and how many applications it rejected.
FROM rejection_reasons r                                                           -- Start from the list of reasons (r is a short name for the table).
LEFT JOIN applications a ON a.rejection_reason_id = r.reason_id                    -- Match each reason to its applications. LEFT JOIN keeps a reason even if nothing used it.
GROUP BY r.reason_id, r.reason, r.rule_number                                      -- One row per reason.
ORDER BY r.reason_id;                                                              -- List them by reason ID.