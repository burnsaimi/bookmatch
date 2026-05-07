-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: bookmatch
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `autores`
--

DROP TABLE IF EXISTS `autores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `autores` (
  `cod_autor` int(11) NOT NULL,
  `nome` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`cod_autor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `autores`
--

LOCK TABLES `autores` WRITE;
/*!40000 ALTER TABLE `autores` DISABLE KEYS */;
/*!40000 ALTER TABLE `autores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracao`
--

DROP TABLE IF EXISTS `configuracao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracao` (
  `estante_publica` tinyint(4) NOT NULL DEFAULT 0,
  `mostrar_localizacao` tinyint(4) NOT NULL DEFAULT 0,
  `mostrar_avaliacao` tinyint(4) NOT NULL DEFAULT 0,
  `ordenar_por` varchar(45) NOT NULL DEFAULT 'compatibilidade',
  `usuario_codusuario` int(11) NOT NULL,
  PRIMARY KEY (`usuario_codusuario`),
  CONSTRAINT `fk_configuracao_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracao`
--

LOCK TABLES `configuracao` WRITE;
/*!40000 ALTER TABLE `configuracao` DISABLE KEYS */;
/*!40000 ALTER TABLE `configuracao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `curtidas`
--

DROP TABLE IF EXISTS `curtidas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curtidas` (
  `codcurtida` int(11) NOT NULL,
  `datacurtida` date DEFAULT NULL,
  `usuario_codusuario` int(11) NOT NULL,
  `estante_cod` int(11) NOT NULL,
  `usuario_codusuario1` int(11) NOT NULL,
  PRIMARY KEY (`codcurtida`),
  KEY `fk_curtidas_usuario1_idx` (`usuario_codusuario`),
  KEY `fk_curtidas_estante1_idx` (`estante_cod`),
  KEY `fk_curtidas_usuario2_idx` (`usuario_codusuario1`),
  CONSTRAINT `fk_curtidas_estante1` FOREIGN KEY (`estante_cod`) REFERENCES `estante` (`cod`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_curtidas_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_curtidas_usuario2` FOREIGN KEY (`usuario_codusuario1`) REFERENCES `usuario` (`codusuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `curtidas`
--

LOCK TABLES `curtidas` WRITE;
/*!40000 ALTER TABLE `curtidas` DISABLE KEYS */;
/*!40000 ALTER TABLE `curtidas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `escreveu`
--

DROP TABLE IF EXISTS `escreveu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `escreveu` (
  `codescreveu` int(11) NOT NULL,
  `livro_codlivro` int(11) NOT NULL,
  `autores_cod_autor` int(11) NOT NULL,
  PRIMARY KEY (`codescreveu`),
  KEY `fk_escreveu_livro1_idx` (`livro_codlivro`),
  KEY `fk_escreveu_autores1_idx` (`autores_cod_autor`),
  CONSTRAINT `fk_escreveu_autores1` FOREIGN KEY (`autores_cod_autor`) REFERENCES `autores` (`cod_autor`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_escreveu_livro1` FOREIGN KEY (`livro_codlivro`) REFERENCES `livro` (`codlivro`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `escreveu`
--

LOCK TABLES `escreveu` WRITE;
/*!40000 ALTER TABLE `escreveu` DISABLE KEYS */;
/*!40000 ALTER TABLE `escreveu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estante`
--

DROP TABLE IF EXISTS `estante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estante` (
  `cod` int(11) NOT NULL,
  `data_adicionado` datetime DEFAULT NULL,
  `livro_codlivro` int(11) NOT NULL,
  `usuario_codusuario` int(11) NOT NULL,
  PRIMARY KEY (`cod`),
  KEY `fk_prateleira_livro_idx` (`livro_codlivro`),
  KEY `fk_prateleira_usuario1_idx` (`usuario_codusuario`),
  CONSTRAINT `fk_prateleira_livro` FOREIGN KEY (`livro_codlivro`) REFERENCES `livro` (`codlivro`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_prateleira_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estante`
--

LOCK TABLES `estante` WRITE;
/*!40000 ALTER TABLE `estante` DISABLE KEYS */;
/*!40000 ALTER TABLE `estante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generos`
--

DROP TABLE IF EXISTS `generos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `generos` (
  `codgen` int(11) NOT NULL,
  `genero` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`codgen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generos`
--

LOCK TABLES `generos` WRITE;
/*!40000 ALTER TABLE `generos` DISABLE KEYS */;
/*!40000 ALTER TABLE `generos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `generos_favo`
--

DROP TABLE IF EXISTS `generos_favo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `generos_favo` (
  `codgen_fav` int(11) NOT NULL,
  `data_adic` datetime DEFAULT NULL,
  `usuario_codusuario` int(11) NOT NULL,
  `generos_codgen` int(11) NOT NULL,
  PRIMARY KEY (`codgen_fav`),
  KEY `fk_generos_favo_usuario1_idx` (`usuario_codusuario`),
  KEY `fk_generos_favo_generos1_idx` (`generos_codgen`),
  CONSTRAINT `fk_generos_favo_generos1` FOREIGN KEY (`generos_codgen`) REFERENCES `generos` (`codgen`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_generos_favo_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generos_favo`
--

LOCK TABLES `generos_favo` WRITE;
/*!40000 ALTER TABLE `generos_favo` DISABLE KEYS */;
/*!40000 ALTER TABLE `generos_favo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livro`
--

DROP TABLE IF EXISTS `livro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `livro` (
  `codlivro` int(11) NOT NULL,
  `nome` varchar(45) DEFAULT NULL,
  `editora` varchar(45) DEFAULT NULL,
  `foto` varchar(70) DEFAULT NULL,
  `genero` varchar(30) DEFAULT NULL,
  `data_lancamento` varchar(4) DEFAULT NULL,
  `imagem` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`codlivro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livro`
--

LOCK TABLES `livro` WRITE;
/*!40000 ALTER TABLE `livro` DISABLE KEYS */;
/*!40000 ALTER TABLE `livro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livros_quer_ler`
--

DROP TABLE IF EXISTS `livros_quer_ler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `livros_quer_ler` (
  `usuario_codusuario` int(11) NOT NULL,
  `livro_codlivro` int(11) NOT NULL,
  `cod_quer_ler` int(11) NOT NULL,
  PRIMARY KEY (`cod_quer_ler`),
  KEY `fk_table1_usuario1_idx` (`usuario_codusuario`),
  KEY `fk_table1_livro1_idx` (`livro_codlivro`),
  CONSTRAINT `fk_table1_livro1` FOREIGN KEY (`livro_codlivro`) REFERENCES `livro` (`codlivro`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livros_quer_ler`
--

LOCK TABLES `livros_quer_ler` WRITE;
/*!40000 ALTER TABLE `livros_quer_ler` DISABLE KEYS */;
/*!40000 ALTER TABLE `livros_quer_ler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `matches`
--

DROP TABLE IF EXISTS `matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `matches` (
  `curtidas_codcurtida` int(11) NOT NULL,
  `curtidas_codcurtida1` int(11) NOT NULL,
  `datamatch` date DEFAULT NULL,
  `codmacth` int(11) NOT NULL,
  PRIMARY KEY (`codmacth`),
  KEY `fk_matches_curtidas1_idx` (`curtidas_codcurtida`),
  KEY `fk_matches_curtidas2_idx` (`curtidas_codcurtida1`),
  CONSTRAINT `fk_matches_curtidas1` FOREIGN KEY (`curtidas_codcurtida`) REFERENCES `curtidas` (`codcurtida`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_matches_curtidas2` FOREIGN KEY (`curtidas_codcurtida1`) REFERENCES `curtidas` (`codcurtida`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matches`
--

LOCK TABLES `matches` WRITE;
/*!40000 ALTER TABLE `matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensagem`
--

DROP TABLE IF EXISTS `mensagem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensagem` (
  `codmensagem` int(11) NOT NULL,
  `usuario_codusuario_remetente` int(11) NOT NULL,
  `usuario_codusuario1_destinatario` int(11) NOT NULL,
  `mensagem` tinytext DEFAULT NULL,
  `data_envio` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codmensagem`),
  KEY `fk_mensagem_usuario1_idx` (`usuario_codusuario_remetente`),
  KEY `fk_mensagem_usuario2_idx` (`usuario_codusuario1_destinatario`),
  CONSTRAINT `fk_mensagem_usuario1` FOREIGN KEY (`usuario_codusuario_remetente`) REFERENCES `usuario` (`codusuario`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_mensagem_usuario2` FOREIGN KEY (`usuario_codusuario1_destinatario`) REFERENCES `usuario` (`codusuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensagem`
--

LOCK TABLES `mensagem` WRITE;
/*!40000 ALTER TABLE `mensagem` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensagem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `codusuario` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `senha` varchar(15) NOT NULL,
  `data_cadastro` datetime DEFAULT NULL,
  `data_ultimo_acesso` datetime DEFAULT NULL,
  `foto` varchar(60) DEFAULT NULL,
  `ativo` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-27 21:22:35
