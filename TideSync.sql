CREATE TABLE `Data` (
  `Added` datetime DEFAULT NULL,
  `DateString` varchar(30) NOT NULL,
  `Type` varchar(15) NOT NULL,
  `Value` int(11) NOT NULL,
  `Payload` varchar(600) NOT NULL,
  `Uploaded` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `Data`
  ADD PRIMARY KEY (`Payload`),
  ADD UNIQUE KEY `Payload` (`Payload`);
