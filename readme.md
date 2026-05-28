# Especificação de Requisitos do Sistema: BookStorys

Este documento apresenta o levantamento detalhado dos Requisitos Funcionais (RF) e Requisitos Não Funcionais (RNF) para o desenvolvimento do sistema de Biblioteca Digital com painel administrativo integrado.

---

## 1. Requisitos Funcionais (RF)

Os requisitos funcionais descrevem o que o sistema deve fazer, ou seja, as funcionalidades e comportamentos que estarão explicitamente disponíveis para as diferentes classes de usuários (Clientes e Funcionários).

| ID | Requisito Funcional | Descrição Detalhada |
| --- | --- | --- |
| **RF-001** | Autenticação de Clientes | O sistema deve permitir que clientes realizem login na página inicial (`index.php`) para acessar de forma autorizada e personalizada a vitrine da biblioteca. |
| **RF-002** | Vitrine de Livros | O sistema deve exibir dinamicamente todos os livros cadastrados no banco de dados. A listagem deve trazer de forma legível e organizada o título, autor, quantidade disponível e a imagem da capa do livro. |
| **RF-003** | Autenticação de Funcionários | O sistema deve possuir uma interface de login específica para o administrador/funcionário (`login_admin.php`), validando as credenciais de acesso por meio do Nome e do Registro do Funcionário (RF). |
| **RF-004** | Cadastro de Funcionários | O sistema deve permitir o registro de novas credenciais de funcionários (`cadastrar_funcionario.php`), validando se o RF inserido já existe no banco de dados para evitar qualquer tipo de duplicidade. |
| **RF-005** | Listagem de Estoque Otimizada | O painel administrativo (`admin_estoque.php`) deve exibir os livros em formato de tabela limpa (otimizada e sem imagens gigantes para não quebrar o layout). A tabela deve exibir título, autor e badges coloridas indicando o status do estoque (Verde para disponível, Vermelho para Esgotado). |
| **RF-006** | Cadastro de Livros | O funcionário autenticado deve conseguir adicionar novos títulos informando Título, Autor, Quantidade em Estoque e o link/caminho da imagem da capa por meio de uma janela flutuante interativa (Modal). |
| **RF-007** | Edição de Livros e Capas | O sistema deve permitir a alteração de todos os dados de um livro existente, incluindo o caminho ou URL da imagem da capa. Os dados atuais devem ser carregados automaticamente dentro da modal de edição ao acionar a ação. |
| **RF-008** | Exclusão de Livros | O funcionário deve poder remover permanentemente um livro do catálogo. O sistema deve acionar uma caixa de confirmação nativa do navegador (Ex: JavaScript `confirm`) antes de concluir a deleção para evitar cliques acidentais. |
| **RF-009** | Encerramento de Sessão (Logout) | O sistema deve conter botões visíveis e acessíveis de "Sair" tanto na vitrine do cliente quanto no painel de controle administrativo para destruir as sessões ativas e redirecionar o usuário para a tela inicial com segurança. |

---

## 2. Requisitos Não Funcionais (RNF)

Os requisitos não funcionais determinam as propriedades, restrições técnicas, critérios de segurança, qualidade e arquitetura que o sistema deve obedecer.

| ID | Requisito Não Funcional | Descrição Técnica e Arquitetural |
| --- | --- | --- |
| **RNF-001** | Persistência de Dados | O sistema deve utilizar obrigatoriamente o Sistema Gerenciador de Banco de Dados (SGBD) **PostgreSQL** para o armazenamento seguro, estruturado e relacional dos dados. |
| **RNF-002** | Segurança contra SQL Injection | Todas as operações de consulta, inserção, atualização ou deleção que envolvem parâmetros vindos do usuário (Inputs) devem utilizar **Prepared Statements via PDO** (PHP Data Objects). |
| **RNF-003** | Segurança contra Cross-Site Scripting (XSS) | Todos os dados recuperados do banco de dados e impressos no corpo do HTML devem ser devidamente tratados e escapados com a função nativa `htmlspecialchars()` do PHP. |
| **RNF-004** | Proteção de Páginas (Controle de Acesso) | Páginas críticas e administrativas como `admin_estoque.php` não podem ser acessadas via URL direta no navegador. O sistema deve validar obrigatoriamente a sessão ativa (`$_SESSION['admin_logged']`) e redirecionar ("chutar") usuários não autorizados para a tela de login. |
| **RNF-005** | Compatibilidade de Imagens Externas | O sistema deve possuir políticas de cabeçalho configuradas (via tag HTML `<meta name="referrer" content="no-referrer">` ou cabeçalhos HTTP) para permitir a renderização perfeita de capas de livros hospedadas em servidores externos da internet, contornando bloqueios comuns de *hotlinking*. |
| **RNF-006** | Usabilidade e Interface (UI/UX) | A interface gráfica global do sistema deve utilizar fontes modernas, transições suaves (CSS transitions) em elementos interativos como botões e links, espaçamento confortável (padding/margin consistentes) e uma paleta de cores harmônica focada em tons de **Muted Indigo/Purple**. |


# Autoavaliação do Aluno: Desenvolvimento de Sistema

## 📋 1. O sistema funciona?

O sistema é **funcional**, consegui corrigir os erros que estavam tendo e a única parte que eu realmente não conseguir editar foi a de adicionar a capa do livro em livros novos, pois os url não estão sendo aceitos.

---

## 🧠 2. Consigo explicar o código?

**Sim, com um pouco de dificuldade mas consigo.**

---

## 🚀 3. Me sinto capaz de desenvolver outro sistema parecido?

**Sim, mas vai demorar para eu conseguir, acredito que estou indo bem no conteúdo mas tem como melhorar.**