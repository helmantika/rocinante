-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 04-11-2017 a las 12:58:38
-- Versión del servidor: 5.7.20-0ubuntu0.16.04.1
-- Versión de PHP: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rocinante`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE PROCEDURE `CacheLangAfterUpdate` ()  MODIFIES SQL DATA
BEGIN
   DECLARE _termid INT(10);
   DECLARE _term VARCHAR(255);
   
   DECLARE _tableid INT(10);
   DECLARE _textid MEDIUMINT(8);
   DECLARE _seqid TINYINT(3);
   
   DECLARE eot INT DEFAULT 1;
   DECLARE temp_eot INT DEFAULT 1;
   
   DECLARE glossary_cursor CURSOR FOR SELECT TermId, Term FROM Glossary;
   DECLARE lang_cursor CURSOR FOR SELECT TableId, TextId, SeqId
                              FROM Lang
                              WHERE (IsNew = 1 OR IsModified = 1) AND En REGEXP CONCAT('[[:<:]]', _term, '[[:>:]]');
   DECLARE CONTINUE HANDLER FOR NOT FOUND SET eot = 0;
   
   DELETE LangGlossary 
   FROM LangGlossary 
   JOIN Lang ON LangGlossary.TableId = Lang.TableId AND 
                LangGlossary.TextId = Lang.TextId AND 
                LangGlossary.SeqId = Lang.SeqId 
   WHERE Lang.IsNew = 1 OR Lang.IsModified = 1 OR Lang.IsDeleted = 1;

   OPEN glossary_cursor;
   WHILE (eot = 1) DO
      FETCH glossary_cursor INTO _termid, _term;
      IF eot = 1 THEN         
         OPEN lang_cursor;
         SET temp_eot = eot;
         WHILE (eot = 1) DO
            FETCH lang_cursor INTO _tableid, _textid, _seqid;
            IF eot = 1 THEN
               INSERT INTO LangGlossary (TableId, TextId, SeqId, TermId) 
                      VALUES (_tableid, _textid, _seqid, _termid);
            END IF;
         END WHILE;
         CLOSE lang_cursor;
         SET eot = temp_eot;
      END IF;
   END WHILE;
   CLOSE glossary_cursor;
END$$

CREATE PROCEDURE `InsertNewLangStrings` ()  MODIFIES SQL DATA
BEGIN
   DECLARE _tableid INT(10);
   DECLARE _textid MEDIUMINT(8);
   DECLARE _seqid TINYINT(3);
   DECLARE _fr TEXT;
   DECLARE _en TEXT;
   
   DECLARE eot INT DEFAULT 1;
   
   DECLARE _cursor CURSOR FOR 
      SELECT NewLang.* FROM NewLang
      LEFT JOIN Lang
      ON (NewLang.TableId = Lang.TableId AND NewLang.TextId = Lang.TextId AND NewLang.SeqId = Lang.SeqId)
      WHERE Lang.TableId IS NULL;

   DECLARE CONTINUE HANDLER FOR NOT FOUND SET eot = 0;

   OPEN _cursor;
   WHILE (eot = 1) DO
      FETCH _cursor INTO _tableid, _textid, _seqid, _fr, _en;
      IF eot = 1 THEN         
         INSERT INTO Lang (TableId, TextId, SeqId, Fr, En, IsNew) 
                VALUES (_tableid, _textid, _seqid, _fr, _en, 1);
      END IF;
   END WHILE;
   CLOSE _cursor;
END$$

CREATE PROCEDURE `UpdateModifiedLangStrings` ()  MODIFIES SQL DATA
BEGIN
   DECLARE _tableid INT(10);
   DECLARE _textid MEDIUMINT(8);
   DECLARE _seqid TINYINT(3);
   DECLARE _fr TEXT;
   DECLARE _en TEXT;
   
   DECLARE eot INT DEFAULT 1;
   
   DECLARE _cursor CURSOR FOR 
      SELECT NewLang.* FROM NewLang
      LEFT JOIN Lang
      ON (NewLang.TableId = Lang.TableId AND NewLang.TextId = Lang.TextId AND NewLang.SeqId = Lang.SeqId)
      WHERE Lang.TableId IS NOT NULL AND NewLang.Fr <> Lang.Fr COLLATE 'utf8_bin';

   DECLARE CONTINUE HANDLER FOR NOT FOUND SET eot = 0;

   OPEN _cursor;
   WHILE (eot = 1) DO
      FETCH _cursor INTO _tableid, _textid, _seqid, _fr, _en;
      IF eot = 1 THEN         
         UPDATE Lang SET Fr=_fr, En=_en, IsModified=1
                     WHERE TableId=_tableid AND TextId=_textid AND SeqId=_seqid;
      END IF;
   END WHILE;
   CLOSE _cursor;
END$$

CREATE PROCEDURE `UpdateDeletedLangStrings` ()  MODIFIES SQL DATA
BEGIN
   DECLARE _tableid INT(10);
   DECLARE _textid MEDIUMINT(8);
   DECLARE _seqid TINYINT(3);
   DECLARE _fr TEXT;
   DECLARE _en TEXT;
   
   DECLARE eot INT DEFAULT 1;
   
   DECLARE _cursor CURSOR FOR 
      SELECT Lang.TableId, Lang.TextId, Lang.SeqId, Lang.Fr, Lang.En FROM Lang
      LEFT JOIN NewLang
      ON (NewLang.TableId = Lang.TableId AND NewLang.TextId = Lang.TextId AND NewLang.SeqId = Lang.SeqId)
      WHERE NewLang.TableId IS NULL;

   DECLARE CONTINUE HANDLER FOR NOT FOUND SET eot = 0;

   OPEN _cursor;
   WHILE (eot = 1) DO
      FETCH _cursor INTO _tableid, _textid, _seqid, _fr, _en;
      IF eot = 1 THEN         
         UPDATE Lang SET Fr=_fr, En=_en, IsDeleted=1
                     WHERE TableId=_tableid AND TextId=_textid AND SeqId=_seqid;
      END IF;
   END WHILE;
   CLOSE _cursor;
END$$

CREATE PROCEDURE `InsertNewLuaStrings` ()  MODIFIES SQL DATA
BEGIN
   DECLARE _textid VARCHAR(255);
   DECLARE _fr VARCHAR(1500);
   DECLARE _en VARCHAR(1500);
   
   DECLARE eot INT DEFAULT 1;
   
   DECLARE _cursor CURSOR FOR 
      SELECT NewLua.* FROM NewLua
      LEFT JOIN Lua
      ON (NewLua.TextId = Lua.TextId)
      WHERE Lua.TextId IS NULL;

   DECLARE CONTINUE HANDLER FOR NOT FOUND SET eot = 0;

   OPEN _cursor;
   WHILE (eot = 1) DO
      FETCH _cursor INTO _textid, _fr, _en;
      IF eot = 1 THEN         
         INSERT INTO Lua (TableId, TextId, Fr, En, IsNew) 
                VALUES (0, _textid, _fr, _en, 1);
      END IF;
   END WHILE;
   CLOSE _cursor;
END$$

CREATE PROCEDURE `UpdateModifiedLuaStrings` ()  MODIFIES SQL DATA
BEGIN
   DECLARE _textid VARCHAR(255);
   DECLARE _fr VARCHAR(1500);
   DECLARE _en VARCHAR(1500);
   
   DECLARE eot INT DEFAULT 1;
   
   DECLARE _cursor CURSOR FOR 
      SELECT NewLua.* FROM NewLua
      LEFT JOIN Lua
      ON (NewLua.TextId = Lua.TextId)
      WHERE Lua.TextId IS NOT NULL AND NewLua.Fr <> Lua.Fr COLLATE 'utf8_bin';

   DECLARE CONTINUE HANDLER FOR NOT FOUND SET eot = 0;

   OPEN _cursor;
   WHILE (eot = 1) DO
      FETCH _cursor INTO _textid, _fr, _en;
      IF eot = 1 THEN         
         UPDATE Lua SET Fr=_fr, En=_en, IsModified=1
                    WHERE TextId=_textid;
      END IF;
   END WHILE;
   CLOSE _cursor;
END$$

CREATE PROCEDURE `UpdateDeletedLuaStrings` ()  MODIFIES SQL DATA
BEGIN
   DECLARE _textid VARCHAR(255);
   DECLARE _fr VARCHAR(1500);
   DECLARE _en VARCHAR(1500);
   
   DECLARE eot INT DEFAULT 1;
   
   DECLARE _cursor CURSOR FOR 
      SELECT Lua.TextId, Lua.Fr, Lua.En FROM Lua
      LEFT JOIN NewLua
      ON (NewLua.TextId = Lua.TextId)
      WHERE NewLua.TextId IS NULL;

   DECLARE CONTINUE HANDLER FOR NOT FOUND SET eot = 0;

   OPEN _cursor;
   WHILE (eot = 1) DO
      FETCH _cursor INTO _textid, _fr, _en;
      IF eot = 1 THEN         
         UPDATE Lua SET Fr=_fr, En=_en, IsDeleted=1
                    WHERE TextId=_textid;
      END IF;
   END WHILE;
   CLOSE _cursor;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `EsoTable`
--

CREATE TABLE `EsoTable` (
  `TableId` int(10) UNSIGNED NOT NULL,
  `Number` smallint(3) UNSIGNED DEFAULT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TypeId` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `Size` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `Translated` float(6,2) NOT NULL DEFAULT '0.00',
  `Revised` float(6,2) NOT NULL DEFAULT '0.00',
  `New` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `Modified` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Glossary`
--

CREATE TABLE `Glossary` (
  `TermId` int(10) UNSIGNED NOT NULL,
  `Term` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Translation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Note` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TypeId` tinyint(3) UNSIGNED NOT NULL,
  `IsLocked` tinyint(1) NOT NULL DEFAULT '0',
  `SingularId` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Disparadores `Glossary`
--
DELIMITER $$
CREATE TRIGGER `CacheLang` AFTER INSERT ON `Glossary` FOR EACH ROW BEGIN   
   
   
   DECLARE _tableid INT(10);
   DECLARE _textid MEDIUMINT(8);
   DECLARE _seqid TINYINT(3);

   DECLARE eot INT DEFAULT 1;

   DECLARE cur CURSOR FOR SELECT TableId, TextId, SeqId 
                          FROM Lang
                          WHERE En REGEXP CONCAT('[[:<:]]', NEW.Term, '[[:>:]]');
   DECLARE CONTINUE HANDLER FOR NOT FOUND SET eot = 0;

   OPEN cur;
   WHILE (eot = 1) DO
      FETCH cur INTO _tableid, _textid, _seqid;
      IF eot = 1 THEN
         INSERT INTO LangGlossary (TableId, TextId, SeqId, TermId) 
                VALUES (_tableid, _textid, _seqid, NEW.TermId);
      END IF;
   END WHILE;
   CLOSE cur;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `DeleteToken` AFTER DELETE ON `Glossary` FOR EACH ROW BEGIN
   DELETE FROM LangGlossary WHERE TermId = OLD.TermId;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Lang`
--

CREATE TABLE `Lang` (
  `TableId` int(10) UNSIGNED NOT NULL,
  `TextId` mediumint(8) UNSIGNED NOT NULL,
  `SeqId` tinyint(3) UNSIGNED NOT NULL,
  `Fr` text COLLATE utf8_unicode_ci,
  `En` text COLLATE utf8_unicode_ci,
  `Es` text COLLATE utf8_unicode_ci,
  `Notes` text COLLATE utf8_unicode_ci,
  `IsAssigned` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Not assigned; 1 - Assigned for translation; 2 - Assigned for revision; 3 - Assigned for both',
  `IsTranslated` tinyint(1) NOT NULL DEFAULT '0',
  `IsRevised` tinyint(1) NOT NULL DEFAULT '0',
  `IsLocked` tinyint(1) NOT NULL DEFAULT '0',
  `IsDisputed` tinyint(1) NOT NULL DEFAULT '0',
  `IsNew` tinyint(1) NOT NULL DEFAULT '0',
  `IsModified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Not modified; 1 - Modified; 2 - Assigned for modification',
  `IsDeleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Disparadores `Lang`
--
DELIMITER $$
CREATE TRIGGER `CreateLang` AFTER INSERT ON `Lang` FOR EACH ROW BEGIN
   INSERT INTO LangSearch ( TableId, TextId, SeqId, Fr, En, Es ) VALUES ( NEW.TableId, NEW.TextId, NEW.SeqId, NEW.Fr, NEW.En, NEW.Es );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `DeleteLang` BEFORE DELETE ON `Lang` FOR EACH ROW BEGIN
   DELETE FROM LangSearch WHERE TableId = OLD.TableId AND TextId = OLD.TextId AND SeqId = OLD.SeqId;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `UpdateLang` AFTER UPDATE ON `Lang` FOR EACH ROW BEGIN
   UPDATE LangSearch SET Fr = NEW.Fr, En =NEW.En, Es = NEW.Es WHERE TableId = NEW.TableId AND TextId = NEW.TextId AND SeqId = NEW.SeqId;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `LangGlossary`
--

CREATE TABLE `LangGlossary` (
  `TableId` int(10) UNSIGNED NOT NULL,
  `TextId` mediumint(8) UNSIGNED NOT NULL,
  `SeqId` tinyint(3) UNSIGNED NOT NULL,
  `TermId` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `LangSearch`
--

CREATE TABLE `LangSearch` (
  `TableId` int(10) UNSIGNED NOT NULL,
  `TextId` mediumint(8) UNSIGNED NOT NULL,
  `SeqId` tinyint(3) UNSIGNED NOT NULL,
  `Fr` text COLLATE utf8_unicode_ci,
  `En` text COLLATE utf8_unicode_ci,
  `Es` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `LangType`
--

CREATE TABLE `LangType` (
  `TypeId` tinyint(3) UNSIGNED NOT NULL,
  `Description` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `Color` varchar(7) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Lua`
--

CREATE TABLE `Lua` (
  `TableId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `TextId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Fr` varchar(1500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `En` varchar(1500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Es` varchar(1500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Notes` text COLLATE utf8_unicode_ci,
  `IsAssigned` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Not assigned; 1 - Assigned for translation; 2 - Assigned for revision; 3 - Assigned for both',
  `IsTranslated` tinyint(1) NOT NULL DEFAULT '0',
  `IsRevised` tinyint(1) NOT NULL DEFAULT '0',
  `IsLocked` tinyint(1) NOT NULL DEFAULT '0',
  `IsDisputed` tinyint(1) NOT NULL DEFAULT '0',
  `IsNew` tinyint(1) NOT NULL DEFAULT '0',
  `IsModified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Not modified; 1 - Modified; 2 - Assigned for modification',
  `IsDeleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Disparadores `Lua`
--
DELIMITER $$
CREATE TRIGGER `CreateLua` AFTER INSERT ON `Lua` FOR EACH ROW BEGIN
   INSERT INTO LuaSearch ( TableId, TextId, Fr, En, Es ) VALUES ( NEW.TableId, NEW.TextId, NEW.Fr, NEW.En, NEW.Es );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `DeleteLua` BEFORE DELETE ON `Lua` FOR EACH ROW BEGIN
   DELETE FROM LuaSearch WHERE TableId = OLD.TableId AND TextId = OLD.TextId;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `UpdateLua` AFTER UPDATE ON `Lua` FOR EACH ROW BEGIN
   UPDATE LuaSearch SET Fr = NEW.Fr, En = NEW.En, Es = NEW.Es WHERE TableId = NEW.TableId AND TextId = NEW.TextId;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `LuaSearch`
--

CREATE TABLE `LuaSearch` (
  `TableId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `TextId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Fr` varchar(1500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `En` varchar(1500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Es` varchar(1500) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Mail`
--

CREATE TABLE `Mail` (
  `MailId` int(10) UNSIGNED NOT NULL,
  `SenderId` smallint(5) UNSIGNED NOT NULL,
  `AddresseeId` smallint(5) UNSIGNED NOT NULL,
  `ChatId` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `MailBox`
--

CREATE TABLE `MailBox` (
  `MailId` int(10) NOT NULL,
  `UserId` smallint(5) NOT NULL,
  `Box` enum('IN','OUT','DRAFT','NOWHERE') COLLATE utf8_unicode_ci NOT NULL,
  `IsRead` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Maintenance`
--

CREATE TABLE `Maintenance` (
  `MaintenanceId` tinyint(4) NOT NULL DEFAULT '0',
  `Active` tinyint(1) NOT NULL DEFAULT '0',
  `Message` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Message`
--

CREATE TABLE `Message` (
  `MailId` int(10) UNSIGNED NOT NULL,
  `Time` datetime NOT NULL,
  `Subject` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Body` varchar(10000) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `MetaTable`
--

CREATE TABLE `MetaTable` (
  `MetaTableId` smallint(5) UNSIGNED NOT NULL,
  `Seq` smallint(5) UNSIGNED NOT NULL,
  `TableId` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `NewLang`
--

CREATE TABLE `NewLang` (
  `TableId` int(10) UNSIGNED NOT NULL,
  `TextId` mediumint(8) UNSIGNED NOT NULL,
  `SeqId` tinyint(3) UNSIGNED NOT NULL,
  `Fr` text COLLATE utf8_unicode_ci,
  `En` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `NewLua`
--

CREATE TABLE `NewLua` (
  `TextId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Fr` varchar(1500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `En` varchar(1500) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Pupil`
--

CREATE TABLE `Pupil` (
  `RelationId` int(10) UNSIGNED NOT NULL,
  `AdvisorId` smallint(5) UNSIGNED NOT NULL,
  `PupilId` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Stats`
--

CREATE TABLE `Stats` (
  `UserId` smallint(5) UNSIGNED NOT NULL,
  `Translated` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `Revised` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `Updated` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `Last` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Status`
--

CREATE TABLE `Status` (
  `StatusId` tinyint(4) NOT NULL DEFAULT '0',
  `Translated` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `Total` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `Percentage` float(6,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Task`
--

CREATE TABLE `Task` (
  `TaskId` int(10) UNSIGNED NOT NULL,
  `TableId` int(10) UNSIGNED DEFAULT NULL,
  `UserId` smallint(5) UNSIGNED NOT NULL,
  `AssignerId` smallint(5) UNSIGNED NOT NULL,
  `Date` date NOT NULL,
  `Type` enum('TRANSLATION','REVISION','UPDATING','GLOSSARY') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'TRANSLATION',
  `Term` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Size` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `Progress` float(6,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `TaskContents`
--

CREATE TABLE `TaskContents` (
  `TaskId` int(10) UNSIGNED NOT NULL,
  `TableId` int(10) UNSIGNED NOT NULL,
  `TextId` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `LuaTextId` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `SeqId` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `Done` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `TermType`
--

CREATE TABLE `TermType` (
  `TypeId` tinyint(3) UNSIGNED NOT NULL,
  `Description` varchar(45) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `User`
--

CREATE TABLE `User` (
  `UserId` smallint(5) UNSIGNED NOT NULL,
  `Username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `FirstName` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `Gender` enum('MALE','FEMALE') COLLATE utf8_unicode_ci NOT NULL,
  `Email` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `SessionId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Type` enum('TRANSLATOR','ADVISOR','ADMIN') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'TRANSLATOR',
  `Theme` varchar(18) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'hot-sneaks',
  `Since` date NOT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Disparadores `User`
--
DELIMITER $$
CREATE TRIGGER `CreateUser` AFTER INSERT ON `User` FOR EACH ROW BEGIN
   INSERT INTO Stats ( UserId, Last ) VALUES ( NEW.UserId,NOW() );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `DeleteUser` BEFORE DELETE ON `User` FOR EACH ROW BEGIN
   DECLARE first_admin SMALLINT(5);

   
   SET first_admin = (SELECT UserId FROM User WHERE Type='ADMIN' LIMIT 1);

   
   DELETE FROM Worker WHERE UserId = OLD.UserId;
   DELETE FROM Mail WHERE SenderId = OLD.UserId OR AddresseeId = OLD.UserId;
   DELETE FROM Task WHERE UserId = OLD.UserId;
   DELETE FROM Stats WHERE UserId = OLD.UserId;
   DELETE FROM Pupil WHERE PupilId = OLD.UserId;
   
   UPDATE Task SET AssignerId = first_admin WHERE AssignerId = OLD.UserId;
   UPDATE Pupil SET AdvisorId = first_admin WHERE AdvisorId = OLD.UserId;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Worker`
--

CREATE TABLE `Worker` (
  `TableId` int(10) UNSIGNED NOT NULL,
  `UserId` smallint(5) UNSIGNED NOT NULL,
  `IsTranslating` tinyint(1) NOT NULL DEFAULT '0',
  `IsRevising` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `Status`
--

INSERT INTO `Status` (`StatusId`, `Translated`, `Total`, `Percentage`) VALUES
(0, 0, 0, 0.0);

--
-- Volcado de datos para la tabla `Maintenance`
--

INSERT INTO `Maintenance` (`MaintenanceId`, `Active`, `Message`) VALUES
(0, 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `EsoTable`
--
ALTER TABLE `EsoTable`
  ADD PRIMARY KEY (`TableId`),
  ADD UNIQUE KEY `Number` (`Number`);

--
-- Indices de la tabla `Glossary`
--
ALTER TABLE `Glossary`
  ADD PRIMARY KEY (`TermId`),
  ADD KEY `Category` (`TypeId`);

--
-- Indices de la tabla `Lang`
--
ALTER TABLE `Lang`
  ADD PRIMARY KEY (`TableId`,`TextId`,`SeqId`);

--
-- Indices de la tabla `LangGlossary`
--
ALTER TABLE `LangGlossary`
  ADD PRIMARY KEY (`TableId`,`TextId`,`SeqId`,`TermId`);

--
-- Indices de la tabla `LangSearch`
--
ALTER TABLE `LangSearch`
  ADD PRIMARY KEY (`TableId`,`TextId`,`SeqId`);
ALTER TABLE `LangSearch` ADD FULLTEXT KEY `En` (`En`);

--
-- Indices de la tabla `LangType`
--
ALTER TABLE `LangType`
  ADD PRIMARY KEY (`TypeId`);

--
-- Indices de la tabla `Lua`
--
ALTER TABLE `Lua`
  ADD PRIMARY KEY (`TextId`);

--
-- Indices de la tabla `LuaSearch`
--
ALTER TABLE `LuaSearch`
  ADD PRIMARY KEY (`TextId`);
ALTER TABLE `LuaSearch` ADD FULLTEXT KEY `En` (`En`);

--
-- Indices de la tabla `Mail`
--
ALTER TABLE `Mail`
  ADD PRIMARY KEY (`MailId`,`SenderId`,`AddresseeId`);

--
-- Indices de la tabla `MailBox`
--
ALTER TABLE `MailBox`
  ADD PRIMARY KEY (`MailId`,`UserId`);

--
-- Indices de la tabla `Maintenance`
--
ALTER TABLE `Maintenance`
  ADD PRIMARY KEY (`MaintenanceId`);

--
-- Indices de la tabla `Message`
--
ALTER TABLE `Message`
  ADD PRIMARY KEY (`MailId`);

--
-- Indices de la tabla `MetaTable`
--
ALTER TABLE `MetaTable`
  ADD PRIMARY KEY (`MetaTableId`,`TableId`);

--
-- Indices de la tabla `NewLang`
--
ALTER TABLE `NewLang`
  ADD PRIMARY KEY (`TableId`,`TextId`,`SeqId`);

--
-- Indices de la tabla `NewLua`
--
ALTER TABLE `NewLua`
  ADD PRIMARY KEY (`TextId`);

--
-- Indices de la tabla `Pupil`
--
ALTER TABLE `Pupil`
  ADD PRIMARY KEY (`RelationId`),
  ADD UNIQUE KEY `UNIQUE` (`AdvisorId`,`PupilId`);

--
-- Indices de la tabla `Stats`
--
ALTER TABLE `Stats`
  ADD PRIMARY KEY (`UserId`);

--
-- Indices de la tabla `Status`
--
ALTER TABLE `Status`
  ADD PRIMARY KEY (`StatusId`);

--
-- Indices de la tabla `Task`
--
ALTER TABLE `Task`
  ADD PRIMARY KEY (`TaskId`),
  ADD KEY `UserId` (`UserId`),
  ADD KEY `AssignerId` (`AssignerId`);

--
-- Indices de la tabla `TaskContents`
--
ALTER TABLE `TaskContents`
  ADD PRIMARY KEY (`TaskId`,`TableId`,`TextId`,`SeqId`,`LuaTextId`);

--
-- Indices de la tabla `TermType`
--
ALTER TABLE `TermType`
  ADD PRIMARY KEY (`TypeId`);

--
-- Indices de la tabla `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`UserId`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indices de la tabla `Worker`
--
ALTER TABLE `Worker`
  ADD PRIMARY KEY (`TableId`,`UserId`),
  ADD KEY `UserId` (`UserId`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `Glossary`
--
ALTER TABLE `Glossary`
  MODIFY `TermId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT de la tabla `Message`
--
ALTER TABLE `Message`
  MODIFY `MailId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT de la tabla `Pupil`
--
ALTER TABLE `Pupil`
  MODIFY `RelationId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT de la tabla `Task`
--
ALTER TABLE `Task`
  MODIFY `TaskId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT de la tabla `TermType`
--
ALTER TABLE `TermType`
  MODIFY `TypeId` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT de la tabla `User`
--
ALTER TABLE `User`
  MODIFY `UserId` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `Glossary`
--
ALTER TABLE `Glossary`
  ADD CONSTRAINT `Glossary_ibfk_1` FOREIGN KEY (`TypeId`) REFERENCES `TermType` (`TypeId`);

--
-- Filtros para la tabla `Task`
--
ALTER TABLE `Task`
  ADD CONSTRAINT `Task_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `User` (`UserId`),
  ADD CONSTRAINT `Task_ibfk_2` FOREIGN KEY (`AssignerId`) REFERENCES `User` (`UserId`);

--
-- Filtros para la tabla `TaskContents`
--
ALTER TABLE `TaskContents`
  ADD CONSTRAINT `TaskContents_ibfk_1` FOREIGN KEY (`TaskId`) REFERENCES `Task` (`TaskId`);

--
-- Filtros para la tabla `Worker`
--
ALTER TABLE `Worker`
  ADD CONSTRAINT `Worker_ibfk_1` FOREIGN KEY (`TableId`) REFERENCES `EsoTable` (`TableId`),
  ADD CONSTRAINT `Worker_ibfk_2` FOREIGN KEY (`UserId`) REFERENCES `User` (`UserId`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
