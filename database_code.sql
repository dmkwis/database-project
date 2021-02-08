DROP TABLE Group_memberships;
DROP TABLE Groups;
DROP TABLE Done_transactions;
DROP TABLE Users;

CREATE TABLE Users(
username VARCHAR2(20) PRIMARY KEY,
name VARCHAR2(20),
surname VARCHAR2(20),
password VARCHAR2(40) NOT NULL,
balance NUMBER(30, 2) NOT NULL
);

CREATE TABLE Done_transactions(
id INTEGER PRIMARY KEY,
source VARCHAR2(20),
destination VARCHAR2(20),
"COMMENT" VARCHAR2(20),
amount NUMBER(30, 2) NOT NULL,
date_of_transaction DATE NOT NULL,
CONSTRAINT source_fk_dt FOREIGN KEY (source) REFERENCES Users(username),
CONSTRAINT destination_fk_dt FOREIGN KEY (destination) REFERENCES Users(username)
);

CREATE TABLE Groups (
group_name VARCHAR2(20) PRIMARY KEY,
group_password VARCHAR(20) NOT NULL,
balance NUMBER(30, 2) NOT NULL
);

CREATE TABLE Group_memberships (
group_name VARCHAR2(20),
username VARCHAR2(20),
CONSTRAINT group_name_fk_gm FOREIGN KEY (group_name) REFERENCES Groups(group_name),
CONSTRAINT username_fk_gm FOREIGN KEY (username) REFERENCES Users(username),
PRIMARY KEY(group_name, username)
);

CREATE OR REPLACE TRIGGER groups_balance_trigger
BEFORE INSERT OR UPDATE ON Groups
FOR EACH ROW
BEGIN
	IF :NEW.balance IS NULL THEN
		raise_application_error(-1, 'Has to have some balance');
	END IF;
	IF :NEW.balance < 0 THEN
		raise_application_error(-2, 'Cannot have negative balance');
	END IF;
END;
/

CREATE OR REPLACE TRIGGER users_balance_trigger
BEFORE INSERT OR UPDATE ON Users
FOR EACH ROW
BEGIN
	IF :NEW.balance IS NULL THEN
		raise_application_error(-1, 'Has to have some balance');
	END IF;
	IF :NEW.balance < 0 THEN
		raise_application_error(-2, 'Cannot have negative balance');
	END IF;
END;
/

CREATE OR REPLACE TRIGGER dt_amount_trigger
BEFORE INSERT ON Done_transactions
FOR EACH ROW
BEGIN
	IF :NEW.amount IS NULL THEN
		raise_application_error(-1, 'Has to have some amount');
	END IF;
	IF :NEW.amount < 0 THEN
		raise_application_error(-2, 'Cannot have negative amount');
	END IF;
END;
/

DROP SEQUENCE id_done_transactions;
DROP SEQUENCE id_regular_transactions;

CREATE SEQUENCE id_done_transactions
START WITH 1
INCREMENT BY 1;

CREATE SEQUENCE id_regular_transactions
START WITH 1
INCREMENT BY 1;

CREATE OR REPLACE FUNCTION username_exists(uname Users.username%TYPE)
                                RETURN NUMBER IS cnt NUMBER;
BEGIN
    SELECT COUNT(*) INTO cnt FROM Users WHERE username = uname;
    RETURN cnt;
END;
/

CREATE OR REPLACE FUNCTION is_member_of_group(uname Group_memberships.username%TYPE,
                                                gname Group_memberships.group_name%TYPE)
                                                RETURN NUMBER IS cnt NUMBER;
BEGIN
    SELECT COUNT(*) INTO cnt FROM Group_memberships WHERE username = uname AND group_name = gname;
    RETURN cnt;
END;
/

CREATE OR REPLACE FUNCTION group_exists(gname Groups.group_name%TYPE)
                                RETURN NUMBER IS cnt NUMBER;
BEGIN
    SELECT COUNT(*) INTO cnt FROM Groups WHERE group_name = gname;
    RETURN cnt;
END;
/

CREATE OR REPLACE FUNCTION is_proper_user(uname Users.username%TYPE,
                                            pword Users.password%TYPE)
                                                RETURN NUMBER IS cnt NUMBER;
BEGIN
    SELECT COUNT(*) INTO cnt FROM Users WHERE username = uname AND password = pword;
    RETURN cnt;
END;
/

CREATE OR REPLACE FUNCTION is_proper_group(gname Groups.group_name%TYPE,
                                            pword Groups.group_password%TYPE)
                                                RETURN NUMBER IS cnt NUMBER;
BEGIN
    SELECT COUNT(*) INTO cnt FROM Groups WHERE group_name = gname AND group_password = pword;
    RETURN cnt;
END;
/

CREATE OR REPLACE PROCEDURE add_user_to_group(uname Users.username%TYPE,
                                              gname Groups.group_name%TYPE,
                                              gpword Groups.group_password%TYPE) IS
BEGIN
    COMMIT;
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    IF is_proper_group(gname, gpword) > 0 THEN
        INSERT INTO Group_memberships VALUES (gname, uname);
    END IF;
    COMMIT;
END;
/

CREATE OR REPLACE PROCEDURE add_group(gname Groups.group_name%TYPE,
                                        pword Groups.group_password%TYPE)
                                        IS
BEGIN
    COMMIT;
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    INSERT INTO Groups VALUES (gname, pword, 0);
    COMMIT;
END;
/



CREATE OR REPLACE PROCEDURE add_user(u_un Users.username%TYPE,
					u_n Users.name%TYPE,
					u_sn Users.surname%TYPE,
					u_pw Users.password%TYPE,
					u_b Users.balance%TYPE) IS
BEGIN
	COMMIT;
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
	INSERT INTO Users VALUES (u_un, u_n, u_sn, u_pw, u_b);
	COMMIT;
END;
/

CREATE OR REPLACE PROCEDURE add_done_transaction(dt_s Done_transactions.source%TYPE,
							dt_d Done_transactions.destination%TYPE,
							dt_c Done_transactions.comment%TYPE,
							dt_a Done_transactions.amount%TYPE,
							dt_dot Done_transactions.date_of_transaction%TYPE) IS
BEGIN
	COMMIT;
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
	INSERT INTO Done_transactions VALUES(id_done_transactions.nextval, dt_s, dt_d, dt_c, dt_a, dt_dot);
	COMMIT;
END;
/

CREATE OR REPLACE PROCEDURE perform_transaction(dt_s Done_transactions.source%TYPE,
							dt_d Done_transactions.destination%TYPE,
							dt_c Done_transactions.comment%TYPE,
							dt_a Done_transactions.amount%TYPE,
							dt_dot VARCHAR2) AS
    temp1 NUMBER;
    temp2 NUMBER;
BEGIN
    COMMIT;
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    IF dt_s <> dt_d AND dt_a > 0 AND dt_dot IS NOT NULL THEN
        SELECT COUNT(*) INTO temp1 FROM Users U WHERE U.username = dt_s AND U.balance >= dt_a;
        SELECT COUNT(*) INTO temp2 FROM Users U WHERE U.username = dt_d;
        IF temp1 <> 0 AND temp2 <> 0 THEN
            INSERT INTO Done_transactions VALUES (id_done_transactions.nextval, dt_s, dt_d, dt_c, dt_a, TO_DATE(dt_dot, 'DD/MM/YYYY'));
            UPDATE Users SET balance = balance - dt_a WHERE username = dt_s;
            UPDATE Users SET balance = balance + dt_a WHERE username = dt_d;
        END IF;
    END IF;
    COMMIT;
END;
/

CREATE OR REPLACE PROCEDURE user_group_transaction(uname Users.username%TYPE,
                                                        gname Groups.group_name%TYPE,
                                                        am Users.balance%TYPE) AS
    temp_u NUMBER;
    temp_g NUMBER;
BEGIN
    COMMIT;
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    IF is_member_of_group(uname, gname) > 0 THEN
       SELECT balance INTO temp_u FROM users WHERE username = uname;
       SELECT balance INTO temp_g FROM Groups WHERE gname = group_name;
       IF temp_u - am >= 0 AND temp_g + am >= 0 THEN
           UPDATE Groups SET balance = balance + am WHERE gname = group_name;
           UPDATE Users SET balance = balance - am WHERE username = uname;
       END IF;
    END IF;
    COMMIT;
END;
/
