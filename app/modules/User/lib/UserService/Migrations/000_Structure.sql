-- -------------------------------------------------------------------
-- Initial structure for UserService in SQL
-- -------------------------------------------------------------------


-- Delete old tables

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS groups;
DROP TABLE IF EXISTS xusergroup;

-- Users

CREATE TABLE IF NOT EXISTS users
(
    user_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT "Internal field to reference datasets",
    loginname VARCHAR(255) NOT NULL UNIQUE COMMENT "Unique loginname",
    realname TEXT NOT NULL COMMENT "Real Name",
    email TEXT NOT NULL COMMENT "E-Mail address",
    pass TEXT NOT NULL COMMENT "password hash",
    locktime INT NOT NULL COMMENT "unix timestamp until the user is locked",
    comment TEXT NOT NULL COMMENT "Free comment"
)
ENGINE = InnoDB;

-- Groups

CREATE TABLE IF NOT EXISTS groups
(
    group_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT "Internal field to reference datasets",
    groupname VARCHAR(255) NOT NULL COMMENT "Unique groupname.applicationId",
    applicationid VARCHAR(255) NOT NULL COMMENT "Unique groupname.applicationId",
    comment TEXT NOT NULL COMMENT "Free comment",
    UNIQUE KEY (groupname, applicationId)
)
ENGINE = InnoDB;

-- reference table

CREATE TABLE IF NOT EXISTS xusergroup
(
    user_id INT NOT NULL COMMENT "Internal field to reference users",
    group_id INT NOT NULL COMMENT "Internal field to reference groups",
    PRIMARY KEY (user_id, group_id)
)
ENGINE = InnoDB;
