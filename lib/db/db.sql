--NOTE: There must be no semi-colons in any of the comments!

--All players that have logged into our site (or that are alliances presidents)
CREATE TABLE IF NOT EXISTS players(
	playerId INT PRIMARY KEY AUTO_INCREMENT,
	
	--the player's name
	name VARCHAR(50) NOT NULL,
	
	--the last time the player logged in
	lastLoginDate DATETIME,
	
	--the IP address of the last login
	lastLoginIP VARCHAR(15)
);

--Contains all alliances in the game, whether they are registered with our site or not.
CREATE TABLE IF NOT EXISTS alliances(
	allianceId INT PRIMARY KEY AUTO_INCREMENT,
	
	--the tag (without brackets)
	tag VARCHAR(5) NOT NULL,

	--the alliance name
	name VARCHAR(50) NOT NULL,
	
	--the alliance president
	president INT NOT NULL REFERENCES players(playerId),
	
	--the date that the president has joined his alliance in hyp-tools
	registeredDate DATETIME,
	
	--the message of the day
	motd VARCHAR(1024)
);

--The permissions each player has within each of their alliances
--Also represents join requests that players make to an alliance
CREATE TABLE IF NOT EXISTS permissions(
	permissionId INT PRIMARY KEY AUTO_INCREMENT,
	
	--the player
	playerId INT NOT NULL REFERENCES players(playerId),
	
	--the alliance
	allianceId INT NOT NULL REFERENCES alliances(allianceId),
	
	/*
	 * 0 = join request sent
	 * 1 = request accepted
	 * 2 = request rejected
	 */
	status INT NOT NULL,
	
	--the date that the request to join the alliance was made
	requestDate DATETIME NOT NULL,
	
	--the date the request was either accepted or rejected
	acceptDate DATETIME,
	
	--true if the player has permission to submit their fleet info
	permSubmit TINYINT(1) NOT NULL DEFAULT 0,
	
	--true if the player has permission to view the entire alliance's fleet info
	permView TINYINT(1) NOT NULL DEFAULT 0,
	
	--true if the player has permission to accept players into their alliance (through our tool--the assumption is that the player is already a member of the alliance) and to set each player's permissions
	permAdmin TINYINT(1) NOT NULL DEFAULT 0
);