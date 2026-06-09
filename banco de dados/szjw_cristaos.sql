-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 05/06/2026 às 17:14
-- Versão do servidor: 5.7.44
-- Versão do PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `szjw_cristaos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `acompanhamento_espiritual`
--

CREATE TABLE `acompanhamento_espiritual` (
  `id` int(11) NOT NULL,
  `id_membro` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_status` int(11) NOT NULL DEFAULT '1',
  `data_acompanhamento` date NOT NULL,
  `responsavel` varchar(150) DEFAULT NULL,
  `situacao_espiritual` varchar(100) DEFAULT NULL,
  `assunto` varchar(150) DEFAULT NULL,
  `observacao` text,
  `proxima_acao` text,
  `data_retorno` date DEFAULT NULL,
  `prioridade` enum('BAIXA','MEDIA','ALTA') NOT NULL DEFAULT 'MEDIA',
  `sigiloso` tinyint(1) NOT NULL DEFAULT '0',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `acompanhamento_status`
--

CREATE TABLE `acompanhamento_status` (
  `id` int(11) NOT NULL,
  `descricao` varchar(50) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Despejando dados para a tabela `acompanhamento_status`
--

INSERT INTO `acompanhamento_status` (`id`, `descricao`, `ativo`) VALUES
(1, 'Aberto', 1),
(2, 'Em acompanhamento', 1),
(3, 'Concluído', 1),
(4, 'Cancelado', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `acompanhamento_tipos`
--

CREATE TABLE `acompanhamento_tipos` (
  `id` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Despejando dados para a tabela `acompanhamento_tipos`
--

INSERT INTO `acompanhamento_tipos` (`id`, `descricao`, `ativo`) VALUES
(1, 'Visita pastoral', 1),
(2, 'Oração', 1),
(3, 'Discipulado', 1),
(4, 'Aconselhamento', 1),
(5, 'Contato telefônico', 1),
(6, 'Acompanhamento de visitante', 1),
(7, 'Ausência em cultos/EBD', 1),
(8, 'Outro', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `atas`
--

CREATE TABLE `atas` (
  `id_ata` int(11) NOT NULL,
  `numero_livro` varchar(25) NOT NULL,
  `reuniao_numero` varchar(25) NOT NULL,
  `data_reuniao` datetime NOT NULL,
  `id_igreja` int(11) NOT NULL,
  `ata_texto` longtext CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Estrutura para tabela `aulas`
--

CREATE TABLE `aulas` (
  `data_aula` datetime NOT NULL,
  `id_aula` int(11) NOT NULL,
  `nome_da_aula` varchar(250) COLLATE latin1_general_ci NOT NULL,
  `id_evento` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


--
-- Estrutura para tabela `cargos`
--

CREATE TABLE `cargos` (
  `id_cargo` int(11) NOT NULL,
  `descricao` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'Diácono'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `cargos`
--

INSERT INTO `cargos` (`id_cargo`, `descricao`) VALUES
(1, 'Diácono'),
(2, 'Licenciados'),
(3, 'Missionários'),
(4, 'Pastores'),
(5, 'Presbíteros em Atividade'),
(6, 'Seminaristas'),
(7, 'Nenhuma Ocupação');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL,
  `nome_do_curso` varchar(250) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `dizimos`
--

CREATE TABLE `dizimos` (
  `id_lancamento` int(11) NOT NULL,
  `data_lancamento` date NOT NULL,
  `id_membro` int(11) NOT NULL,
  `valor_dizimo` float NOT NULL,
  `id_lancamento_financeiro` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL,
  `descricao` varchar(100) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `grupos`
--

CREATE TABLE `grupos` (
  `id_grupo` int(11) NOT NULL,
  `descricao` varchar(80) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `grupos`
--

INSERT INTO `grupos` (`id_grupo`, `descricao`) VALUES
(1, 'Dízimos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_membro`
--

CREATE TABLE `historico_membro` (
  `id` int(11) NOT NULL,
  `id_membro` int(11) NOT NULL,
  `status` enum('Ativo','Inativo','Transferido','Desligado','Excluído','Falecido') NOT NULL,
  `motivo` enum('Abandono','Transferência','Disciplina','Solicitação','Falecimento','Não Localizado') NOT NULL,
  `observacao` mediumtext NOT NULL,
  `data_evento` date NOT NULL,
  `numero_livro_ata` int(11) NOT NULL,
  `numero_ata` int(11) NOT NULL,
  `cadastrado_por` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `igrejas`
--

CREATE TABLE `igrejas` (
  `id_igreja` int(11) NOT NULL,
  `nome` varchar(200) COLLATE latin1_general_ci NOT NULL,
  `denominacao` varchar(200) COLLATE latin1_general_ci NOT NULL,
  `pais` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `estado` varchar(2) COLLATE latin1_general_ci NOT NULL,
  `municipio` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `endereco` varchar(250) COLLATE latin1_general_ci NOT NULL,
  `cep` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `latitude` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `longitude` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `lancamentos`
--

CREATE TABLE `lancamentos` (
  `id_lancamento` int(11) NOT NULL,
  `documento_numero` varchar(15) NOT NULL,
  `data_lancamento` date NOT NULL,
  `descricao` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tipo` enum('Pagar','Receber') NOT NULL,
  `data_vencimento` date NOT NULL,
  `valor_nominal` float NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `valor_pago` float DEFAULT NULL,
  `status` enum('Aberto','Recebido','Pago') NOT NULL,
  `forma_de_pagamento_recebimento` enum('Pix Recebido','Pix QR Code','Aplicação','Cartão Débito','Débito Automático','Crédito em Conta','Débito em Conta','Pagamento Boleto','Pix Pagamento','Transação Bancária') DEFAULT NULL,
  `id_grupo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `membros`
--

CREATE TABLE `membros` (
  `id_membro` int(11) NOT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `id_igreja` int(11) NOT NULL,
  `nome_do_membro` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `telefone` bigint(50) DEFAULT NULL,
  `sexo` enum('Masculino','Feminino') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `nacionalidade` varchar(80) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `naturalidade` varchar(80) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `nome_do_pai` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `nome_da_mae` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `tipo_sanguineo` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `estado_civil` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `cep` int(8) DEFAULT NULL,
  `endereco` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `cidade` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `estado` varchar(2) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `data_batismo` date DEFAULT NULL,
  `data_profissao_de_fe` date DEFAULT NULL,
  `id_cargo` int(11) NOT NULL,
  `status_atual` varchar(80) NOT NULL,
  `data_cadastro` timestamp(1) NOT NULL DEFAULT CURRENT_TIMESTAMP(1) ON UPDATE CURRENT_TIMESTAMP(1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Estrutura para tabela `presencas`
--

CREATE TABLE `presencas` (
  `data_aula` datetime NOT NULL,
  `id_presenca` int(11) NOT NULL,
  `id_membro` int(11) NOT NULL,
  `id_aula` int(11) NOT NULL,
  `id_professor` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_cargo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `presencas_atas`
--

CREATE TABLE `presencas_atas` (
  `id_presenca` int(11) NOT NULL,
  `Id_ata` int(11) NOT NULL,
  `id_membro` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores`
--

CREATE TABLE `professores` (
  `id_professor` int(11) NOT NULL,
  `nome_do_professor` varchar(250) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo`
--

CREATE TABLE `tipo` (
  `id_tipo` int(11) NOT NULL,
  `descricao` varchar(80) COLLATE latin1_general_ci NOT NULL DEFAULT 'Primeira Vez'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Despejando dados para a tabela `tipo`
--

INSERT INTO `tipo` (`id_tipo`, `descricao`) VALUES
(2, 'Visitante'),
(3, 'Membro Professo'),
(4, 'Membro Não Professo'),
(5, 'Primeira Vez');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome_usuario` varchar(150) CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `perfil` enum('ADMIN','OPERADOR','CONSULTA','LIDER','CONSELHO') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `visitantes`
--

CREATE TABLE `visitantes` (
  `id_visitante` int(11) NOT NULL,
  `nome` varchar(150) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `id_membro` int(11) NOT NULL,
  `sexo` enum('Masculino','Feminino') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `cidade` varchar(80) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `endereco` varchar(200) NOT NULL,
  `oracao` text NOT NULL,
  `data_cadastro` datetime NOT NULL,
  `id_evento` int(11) NOT NULL,
  `cadastrante` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `acompanhamento_espiritual`
--
ALTER TABLE `acompanhamento_espiritual`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_acomp_tipo` (`id_tipo`),
  ADD KEY `idx_acomp_membro` (`id_membro`),
  ADD KEY `idx_acomp_data` (`data_acompanhamento`),
  ADD KEY `idx_acomp_status` (`id_status`),
  ADD KEY `idx_acomp_retorno` (`data_retorno`);

--
-- Índices de tabela `acompanhamento_status`
--
ALTER TABLE `acompanhamento_status`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `acompanhamento_tipos`
--
ALTER TABLE `acompanhamento_tipos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `atas`
--
ALTER TABLE `atas`
  ADD PRIMARY KEY (`id_ata`),
  ADD KEY `id_igreja` (`id_igreja`);

--
-- Índices de tabela `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id_aula`),
  ADD UNIQUE KEY `data_aula` (`data_aula`),
  ADD KEY `id_evento` (`id_evento`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `data_aula_2` (`data_aula`);

--
-- Índices de tabela `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id_cargo`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`);

--
-- Índices de tabela `dizimos`
--
ALTER TABLE `dizimos`
  ADD PRIMARY KEY (`id_lancamento`),
  ADD KEY `id_membro` (`id_membro`);

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id_evento`);

--
-- Índices de tabela `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id_grupo`);

--
-- Índices de tabela `historico_membro`
--
ALTER TABLE `historico_membro`
  ADD KEY `historico_membro` (`id_membro`);

--
-- Índices de tabela `igrejas`
--
ALTER TABLE `igrejas`
  ADD PRIMARY KEY (`id_igreja`);

--
-- Índices de tabela `lancamentos`
--
ALTER TABLE `lancamentos`
  ADD PRIMARY KEY (`id_lancamento`),
  ADD KEY `id_grupo` (`id_grupo`);

--
-- Índices de tabela `membros`
--
ALTER TABLE `membros`
  ADD PRIMARY KEY (`id_membro`),
  ADD UNIQUE KEY `codigo_barras` (`codigo_barras`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `id_cargo` (`id_cargo`),
  ADD KEY `id_igreja` (`id_igreja`) USING BTREE;

--
-- Índices de tabela `presencas`
--
ALTER TABLE `presencas`
  ADD PRIMARY KEY (`id_presenca`),
  ADD KEY `data` (`data_aula`),
  ADD KEY `id_membro` (`id_membro`),
  ADD KEY `id_professor` (`id_professor`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `id_cargo` (`id_cargo`),
  ADD KEY `id_aula` (`id_aula`);

--
-- Índices de tabela `presencas_atas`
--
ALTER TABLE `presencas_atas`
  ADD PRIMARY KEY (`id_presenca`),
  ADD KEY `id_membro` (`id_membro`),
  ADD KEY `Id_ata` (`Id_ata`);

--
-- Índices de tabela `professores`
--
ALTER TABLE `professores`
  ADD PRIMARY KEY (`id_professor`);

--
-- Índices de tabela `tipo`
--
ALTER TABLE `tipo`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `visitantes`
--
ALTER TABLE `visitantes`
  ADD PRIMARY KEY (`id_visitante`),
  ADD KEY `id_membro` (`id_membro`),
  ADD KEY `id_evento` (`id_evento`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `acompanhamento_espiritual`
--
ALTER TABLE `acompanhamento_espiritual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `acompanhamento_status`
--
ALTER TABLE `acompanhamento_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `acompanhamento_tipos`
--
ALTER TABLE `acompanhamento_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `atas`
--
ALTER TABLE `atas`
  MODIFY `id_ata` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id_aula` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `grupos`
--
ALTER TABLE `grupos`
  MODIFY `id_grupo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `igrejas`
--
ALTER TABLE `igrejas`
  MODIFY `id_igreja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `membros`
--
ALTER TABLE `membros`
  MODIFY `id_membro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `presencas`
--
ALTER TABLE `presencas`
  MODIFY `id_presenca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `presencas_atas`
--
ALTER TABLE `presencas_atas`
  MODIFY `id_presenca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `professores`
--
ALTER TABLE `professores`
  MODIFY `id_professor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `tipo`
--
ALTER TABLE `tipo`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT de tabela `visitantes`
--
ALTER TABLE `visitantes`
  MODIFY `id_visitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `acompanhamento_espiritual`
--
ALTER TABLE `acompanhamento_espiritual`
  ADD CONSTRAINT `fk_acomp_membro` FOREIGN KEY (`id_membro`) REFERENCES `membros` (`id_membro`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_acomp_status` FOREIGN KEY (`id_status`) REFERENCES `acompanhamento_status` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_acomp_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `acompanhamento_tipos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `atas`
--
ALTER TABLE `atas`
  ADD CONSTRAINT `atas_ibfk_1` FOREIGN KEY (`id_igreja`) REFERENCES `igrejas` (`id_igreja`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `aulas_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
  ADD CONSTRAINT `aulas_ibfk_2` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`);

--
-- Restrições para tabelas `dizimos`
--
ALTER TABLE `dizimos`
  ADD CONSTRAINT `dizimos_ibfk_1` FOREIGN KEY (`id_membro`) REFERENCES `membros` (`id_membro`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `historico_membro`
--
ALTER TABLE `historico_membro`
  ADD CONSTRAINT `historico_membro` FOREIGN KEY (`id_membro`) REFERENCES `membros` (`id_membro`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `lancamentos`
--
ALTER TABLE `lancamentos`
  ADD CONSTRAINT `lancamentos_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `membros`
--
ALTER TABLE `membros`
  ADD CONSTRAINT `membros_ibfk_1` FOREIGN KEY (`id_igreja`) REFERENCES `igrejas` (`id_igreja`),
  ADD CONSTRAINT `membros_ibfk_3` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`),
  ADD CONSTRAINT `membros_ibfk_4` FOREIGN KEY (`id_tipo`) REFERENCES `tipo` (`id_tipo`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `presencas`
--
ALTER TABLE `presencas`
  ADD CONSTRAINT `presencas_ibfk_1` FOREIGN KEY (`id_professor`) REFERENCES `professores` (`id_professor`),
  ADD CONSTRAINT `presencas_ibfk_2` FOREIGN KEY (`id_membro`) REFERENCES `membros` (`id_membro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presencas_ibfk_3` FOREIGN KEY (`id_tipo`) REFERENCES `tipo` (`id_tipo`),
  ADD CONSTRAINT `presencas_ibfk_4` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`),
  ADD CONSTRAINT `presencas_ibfk_5` FOREIGN KEY (`id_aula`) REFERENCES `aulas` (`id_aula`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `presencas_atas`
--
ALTER TABLE `presencas_atas`
  ADD CONSTRAINT `presencas_atas_ibfk_1` FOREIGN KEY (`Id_ata`) REFERENCES `atas` (`id_ata`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `visitantes`
--
ALTER TABLE `visitantes`
  ADD CONSTRAINT `visitantes_ibfk_1` FOREIGN KEY (`id_membro`) REFERENCES `tipo` (`id_tipo`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `visitantes_ibfk_2` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
