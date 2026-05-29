CREATE DATABASE  IF NOT EXISTS `bookmatch` /*!40100 DEFAULT CHARACTER SET utf8mb3 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `bookmatch`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: bookmatch
-- ------------------------------------------------------
-- Server version	8.0.46

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
  `cod_autor` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`cod_autor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `estante_publica` tinyint NOT NULL DEFAULT '0',
  `mostrar_localizacao` tinyint NOT NULL DEFAULT '0',
  `mostrar_avaliacao` tinyint NOT NULL DEFAULT '0',
  `ordenar_por` varchar(45) NOT NULL DEFAULT 'compatibilidade',
  `usuario_codusuario` int NOT NULL,
  PRIMARY KEY (`usuario_codusuario`),
  CONSTRAINT `fk_configuracao_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `codcurtida` int NOT NULL AUTO_INCREMENT,
  `datacurtida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_codusuario` int NOT NULL,
  `estante_cod` int NOT NULL,
  `usuario_codusuario1` int NOT NULL,
  PRIMARY KEY (`codcurtida`),
  KEY `fk_curtidas_usuario1_idx` (`usuario_codusuario`),
  KEY `fk_curtidas_estante1_idx` (`estante_cod`),
  KEY `fk_curtidas_usuario2_idx` (`usuario_codusuario1`),
  CONSTRAINT `fk_curtidas_estante1` FOREIGN KEY (`estante_cod`) REFERENCES `estante` (`cod`),
  CONSTRAINT `fk_curtidas_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`),
  CONSTRAINT `fk_curtidas_usuario2` FOREIGN KEY (`usuario_codusuario1`) REFERENCES `usuario` (`codusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `codescreveu` int NOT NULL,
  `livro_codlivro` int NOT NULL AUTO_INCREMENT,
  `autores_cod_autor` int NOT NULL,
  PRIMARY KEY (`codescreveu`),
  KEY `fk_escreveu_livro1_idx` (`livro_codlivro`),
  KEY `fk_escreveu_autores1_idx` (`autores_cod_autor`),
  CONSTRAINT `fk_escreveu_autores1` FOREIGN KEY (`autores_cod_autor`) REFERENCES `autores` (`cod_autor`),
  CONSTRAINT `fk_escreveu_livro1` FOREIGN KEY (`livro_codlivro`) REFERENCES `livro` (`codlivro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `cod` int NOT NULL AUTO_INCREMENT,
  `data_adicionado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `livro_codlivro` int NOT NULL,
  `usuario_codusuario` int NOT NULL,
  PRIMARY KEY (`cod`),
  KEY `fk_prateleira_livro_idx` (`livro_codlivro`),
  KEY `fk_prateleira_usuario1_idx` (`usuario_codusuario`),
  CONSTRAINT `fk_prateleira_livro` FOREIGN KEY (`livro_codlivro`) REFERENCES `livro` (`codlivro`),
  CONSTRAINT `fk_prateleira_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `codgen` int NOT NULL AUTO_INCREMENT,
  `genero` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`codgen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `codgen_fav` int NOT NULL AUTO_INCREMENT,
  `data_adic` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_codusuario` int NOT NULL,
  `generos_codgen` int NOT NULL,
  PRIMARY KEY (`codgen_fav`),
  KEY `fk_generos_favo_usuario1_idx` (`usuario_codusuario`),
  KEY `fk_generos_favo_generos1_idx` (`generos_codgen`),
  CONSTRAINT `fk_generos_favo_generos1` FOREIGN KEY (`generos_codgen`) REFERENCES `generos` (`codgen`),
  CONSTRAINT `fk_generos_favo_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `generos_favo`
--

LOCK TABLES `generos_favo` WRITE;
/*!40000 ALTER TABLE `generos_favo` DISABLE KEYS */;
/*!40000 ALTER TABLE `generos_favo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imagem_livro_usuario`
--

DROP TABLE IF EXISTS `imagem_livro_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imagem_livro_usuario` (
  `cod_imagem_livro_usuario` int NOT NULL AUTO_INCREMENT,
  `imagem` varchar(80) DEFAULT NULL,
  `estante_cod` int NOT NULL,
  PRIMARY KEY (`cod_imagem_livro_usuario`),
  KEY `fk_imagem_livro_usuario_estante1_idx` (`estante_cod`),
  CONSTRAINT `fk_imagem_livro_usuario_estante1` FOREIGN KEY (`estante_cod`) REFERENCES `estante` (`cod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imagem_livro_usuario`
--

LOCK TABLES `imagem_livro_usuario` WRITE;
/*!40000 ALTER TABLE `imagem_livro_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `imagem_livro_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livro`
--

DROP TABLE IF EXISTS `livro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `livro` (
  `codlivro` int NOT NULL AUTO_INCREMENT,
  `isbn` varchar(13) NOT NULL,
  `nome` varchar(45) DEFAULT NULL,
  `editora` varchar(45) DEFAULT NULL,
  `foto_api` varchar(70) DEFAULT NULL,
  `genero` varchar(30) DEFAULT NULL,
  `data_lancamento` year DEFAULT NULL,
  PRIMARY KEY (`codlivro`),
  KEY `ISBN` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `usuario_codusuario` int NOT NULL AUTO_INCREMENT,
  `livro_codlivro` int NOT NULL,
  `cod_quer_ler` int NOT NULL,
  PRIMARY KEY (`cod_quer_ler`),
  KEY `fk_table1_usuario1_idx` (`usuario_codusuario`),
  KEY `fk_table1_livro1_idx` (`livro_codlivro`),
  CONSTRAINT `fk_table1_livro1` FOREIGN KEY (`livro_codlivro`) REFERENCES `livro` (`codlivro`),
  CONSTRAINT `fk_table1_usuario1` FOREIGN KEY (`usuario_codusuario`) REFERENCES `usuario` (`codusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `codmacth` int NOT NULL,
  `curtidas_codcurtida` int NOT NULL AUTO_INCREMENT,
  `curtidas_codcurtida1` int NOT NULL,
  `datamatch` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`codmacth`),
  KEY `fk_matches_curtidas1_idx` (`curtidas_codcurtida`),
  KEY `fk_matches_curtidas2_idx` (`curtidas_codcurtida1`),
  CONSTRAINT `fk_matches_curtidas1` FOREIGN KEY (`curtidas_codcurtida`) REFERENCES `curtidas` (`codcurtida`),
  CONSTRAINT `fk_matches_curtidas2` FOREIGN KEY (`curtidas_codcurtida1`) REFERENCES `curtidas` (`codcurtida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `codmensagem` int NOT NULL AUTO_INCREMENT,
  `usuario_codusuario_remetente` int NOT NULL,
  `usuario_codusuario_destinatario` int NOT NULL,
  `mensagem` tinytext,
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`codmensagem`),
  KEY `fk_mensagem_usuario1_idx` (`usuario_codusuario_remetente`),
  KEY `fk_mensagem_usuario2_idx` (`usuario_codusuario_destinatario`),
  CONSTRAINT `fk_mensagem_usuario1` FOREIGN KEY (`usuario_codusuario_remetente`) REFERENCES `usuario` (`codusuario`),
  CONSTRAINT `fk_mensagem_usuario2` FOREIGN KEY (`usuario_codusuario_destinatario`) REFERENCES `usuario` (`codusuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensagem`
--

LOCK TABLES `mensagem` WRITE;
/*!40000 ALTER TABLE `mensagem` DISABLE KEYS */;
INSERT INTO `mensagem` VALUES (1,1,2,'Oi Felipe, você conseguiu terminar a parte da modelagem do banco?','2026-05-11 23:36:39'),(2,2,1,'Consegui sim, Gabriel! Já atualizei o script sem a coluna de foto.','2026-05-11 23:36:39'),(3,1,2,'Ótimo. Você tem aquele livro sobre engenharia de software para me emprestar?','2026-05-11 23:36:39'),(4,2,1,'Tenho sim, levo na universidade amanhã para você.','2026-05-11 23:36:39'),(5,3,1,'Olá! Vi que você tem interesse em história da imigração, tem algum livro para recomendar?','2026-05-11 23:36:39');
/*!40000 ALTER TABLE `mensagem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `codusuario` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `senha` varchar(15) NOT NULL,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_ultimo_acesso` datetime DEFAULT NULL,
  `foto` varchar(60) DEFAULT NULL,
  `ativo` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`codusuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Gabriel','gabriel@email.com','Senha123!','2026-05-11 23:32:04',NULL,NULL,1),(2,'Felipe','felipe@email.com','Proj2026*','2026-05-11 23:32:04',NULL,NULL,1),(3,'Usuario Teste','teste@bookmatch.com','teste123','2026-05-11 23:32:04',NULL,NULL,1),(4,'Admin Sistema','admin@bookmatch.com','admin_root','2026-05-11 23:32:04',NULL,NULL,1),(5,'Pedro de Alcântara Francisco António João Car','Pedro@gmail.com','123456789','2026-05-12 22:32:08',NULL,NULL,1);
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

-- Dump completed on 2026-05-25 18:58:50
