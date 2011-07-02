--NOTE: There must be no semi-colons in any of the comments!

--All Hyperiums games (such as "Hyperiums6")
CREATE TABLE IF NOT EXISTS games(
	gameId INT PRIMARY KEY AUTO_INCREMENT,
	
	--the game's name
	name VARCHAR(50) NOT NULL,
	
	--the game's description
	description VARCHAR(128)
);

--All players that have logged into our site (or that are alliances presidents)
CREATE TABLE IF NOT EXISTS players(
	playerId INT PRIMARY KEY AUTO_INCREMENT,
	
	--the game that this player belongs to
	gameId INT NOT NULL REFERENCES games(gameId),
	
	--The Hyperiums player ID
	hypPlayerId INT,
	
	--the player's name
	name VARCHAR(50) NOT NULL,
	
	--the last time the player logged in
	lastLoginDate DATETIME
);

--Contains all alliances in the game, whether they are registered with our site or not.
CREATE TABLE IF NOT EXISTS alliances(
	allianceId INT PRIMARY KEY AUTO_INCREMENT,
	
	--the game that this alliance belongs to
	gameId INT NOT NULL REFERENCES games(gameId),
	
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

--Contains all requests that the players make to join alliances
CREATE TABLE IF NOT EXISTS joinRequests(
	joinRequestId INT PRIMARY KEY AUTO_INCREMENT,
	
	--the player who made the request
	playerId INT NOT NULL REFERENCES players(playerId),
	
	--the alliance that the player has requested to join
	allianceId INT NOT NULL REFERENCES alliances(allianceId),
	
	--the date that the join request was made
	requestDate DATETIME NOT NULL
);

--The permissions each player has within each of their alliances
--Also represents join requests that players make to an alliance
CREATE TABLE IF NOT EXISTS permissions(
	permissionId INT PRIMARY KEY AUTO_INCREMENT,
	
	--the player
	playerId INT NOT NULL REFERENCES players(playerId),
	
	--the alliance
	allianceId INT NOT NULL REFERENCES alliances(allianceId),
	
	--the date the player joined the alliance
	joinDate DATETIME NOT NULL,
	
	/* 
	bitmask representing the priviledges the player has in this alliance
	1 = submit - can submit fleet, trading, infil info
	2 = view - can view alliance members' fleet, trading, infil info
	4 = admin - can approve auth requests and set member permissions
	*/
	perms INT NOT NULL DEFAULT 0
);