CREATE DATABASE is218regsite;

CREATE TABLE RegSiteUsers(
  userId int(11) NOT NULL AUTO_INCREMENT,
  username varchar(20) NOT NULL,
  password varchar(20) NOT NULL,
  email varchar(255) NOT NULL,
  active boolean NOT NULL,
  PRIMARY KEY (memberID)
)

INSERT INTO RegSiteUsers Values(
    1, 'TEST', 'testpasswd', 'jac84@njit.edu', true
)
