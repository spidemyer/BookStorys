## 1. Introdução

### 1.1 Contextualização e Propósito do Sistema

O **BookStorys** é uma plataforma de Biblioteca Digital integrada a um ecossistema de gerenciamento de estoque que visa estreitar o relacionamento entre leitores (clientes) e administradores de acervos (funcionários/bibliotecários). O propósito central do sistema é oferecer uma experiência de navegação fluida, segura e esteticamente agradável para a descoberta de títulos literários, ao mesmo tempo que disponibiliza um painel de controle administrativo robusto, simplificado e de alto desempenho para a governança do inventário de livros.

### 1.2 Visão Geral do Ecossistema

O sistema divide-se fundamentalmente em duas grandes frentes operacionais:

1. **A Vitrine Digital do Cliente (Frontend):** Focada na acessibilidade, usabilidade e renderização dinâmica do catálogo. Permite que o usuário autenticado visualize livros disponíveis em tempo real com informações detalhadas e layout responsivo.
2. **O Painel Administrativo do Estoque (Backend):** Área restrita dedicada ao controle do ciclo de vida dos produtos (CRUD completo de livros) e ao gerenciamento de operadores (funcionários), blindada por protocolos de autenticação e proteção de sessão.

### 1.3 Arquitetura de Alto Nível

O BookStorys é estruturado seguindo o modelo de arquitetura web monolítica modular baseada em PHP, utilizando o padrão **PDO (PHP Data Objects)** para isolamento da camada de persistência. O banco de dados relacional escolhido é o **PostgreSQL**, garantindo integridade referencial, conformidade ACID e escalabilidade horizontal para grandes volumes de metadados literários.

---

## 2. Engenharia de Requisitos

### 2.1 Requisitos Funcionais (RF)

Os requisitos funcionais determinam as ações computacionais, comportamentos esperados e fluxos de dados explícitos que a plataforma disponibiliza para seus atores.

| ID | Requisito Funcional | Ator Beneficiário | Descrição Detalhada e Regras de Negócio |
| --- | --- | --- | --- |
| **RF-001** | Autenticação e Restrições do Cliente | Cliente | O sistema deve permitir que clientes realizem login na página inicial (`index.php`). A validação deve criar uma sessão exclusiva de cliente. <br>
| **RF-002** | Vitrine Dinâmica de Livros | Cliente | Exibição de forma dinâmica e assíncrona/síncrona de todos os livros ativos no banco de dados. A listagem deve renderizar obrigatoriamente: Título, Autor, Quantidade disponível em tempo real e a imagem da capa do livro. <br>
| **RF-003** | Autenticação de Funcionários | Funcionário/Admin | Interface de autenticação isolada (login_admin.php). A validação exige o preenchimento obrigatório do Nome do Funcionário e do Registro do Funcionário (RF) numérico. <br>
| **RF-004** | Cadastro de Funcionários Restrito | Funcionário / Admin | Registro de novos operadores via cadastrar_funcionario.php.Regras de Negócio:1. Controle de Acesso: Um funcionário só pode cadastrar outro operador se estiver autenticado/logado no sistema.2. Unicidade: O sistema deve efetuar uma busca prévia no PostgreSQL; se o RF digitado já existir, a operação é abortada e um alerta de duplicidade é retornado. <br>
| **RF-005** | Listagem de Estoque Otimizada | Funcionário / Admin | Painel admin_estoque.php deve exibir os livros em formato de tabela compacta e limpa (sem imagens para otimizar largura). Deve conter badges dinâmicas: Verde (Disponível) se Qtd > 0, e Vermelho (Esgotado) se Qtd = 0. <br>
| **RF-006** |Cadastro de Livros (Modal) | Funcionário / Admin | Inserção de novos títulos na base de dados por meio de uma janela flutuante (Bootstrap/CSS Modal) sem recarregar a página base. Campos obrigatórios: Título, Autor, Quantidade e URL/Caminho da imagem da capa. <br>
| **RF-007** | Edição de Livros e Capas | Funcionário / Admin | Alteração de metadados e caminhos de imagens de livros existentes. Ao clicar em editar, os dados atuais do registro selecionado devem ser populados via JavaScript/DOM para dentro dos inputs da modal de edição. <br>
| **RF-008** | Exclusão de Livros com Confirmação | Funcionário / Admin | Remoção lógica ou física do livro do catálogo. Regra de Segurança: O sistema deve interceptar o clique com um diálogo de confirmação nativo do navegador (window.confirm()). A exclusão só ocorre se o usuário confirmar explicitamente. <br>
| **RF-009** | Encerramento de Sessão (Logout) | Ambos | Botões explícitos e visíveis de ""Sair"". Ao acionar, a aplicação deve invocar session_destroy(), limpar o array $_SESSION, remover os cookies de sessão correspondentes e redirecionar para a raiz pública do projeto. <br>
<br>

<br>**Regras de Negócio de Cadastro/Login:**<br>

<br>1. A senha do cliente deve possuir obrigatoriamente entre **6 e 10 dígitos** (mínimo de 6 e máximo de 10).<br>

<br>2. A idade do cliente deve estar estritamente no intervalo entre **0 e 100 anos**. Cadastros fora desses parâmetros serão rejeitados. <br>

<br>

<br>**Regras de Negócio:**<br>

<br>1. **Controle de Acesso:** Um funcionário só pode cadastrar outro operador se estiver **autenticado/logado** no sistema.<br>

<br>2. **Unicidade:** O sistema deve efetuar uma busca prévia no PostgreSQL; se o RF digitado já existir, a operação é abortada e um alerta de duplicidade é retornado.

### 2.2 Requisitos Não Funcionais (RNF)

Os requisitos não funcionais especificam restrições técnicas, qualidades arquiteturais e métricas de segurança cibernética que o sistema deve cumprir para manter sua integridade.

| ID | Categoria | Requisito Não Funcional | Especificação Técnica e Arquitetural |
| --- | --- | --- | --- |
| **RNF-001** | Banco de Dados | Persistência Relacional | O sistema deve utilizar estritamente o SGBD **PostgreSQL** em sua versão estável (14+), garantindo isolamento transacional e consistência do inventário. |
| **RNF-002** | Segurança | Prevenção contra SQL Injection | Fica terminantemente proibida a concatenação direta de variáveis em strings SQL. Todas as queries de consulta ou mutação (DML) devem usar **Prepared Statements parametrizados via PDO**. |
| **RNF-003** | Segurança | Mitigação de Ataques XSS | Toda e qualquer saída de dados originada do banco de dados ou de parâmetros de requisição renderizados no HTML deve passar obrigatoriamente pela função `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`. |
| **RNF-004** | Controle de Acesso | Proteção de Rotas e Validações Backend | 1. Páginas do ecossistema administrativo (ex: `admin_estoque.php`, `cadastrar_funcionario.php`) devem verificar no topo do arquivo a existência e a veracidade da variável global `$_SESSION['admin_logged']`. Caso ausente, o fluxo é interrompido com `header('Location: login_admin.php')` e `exit;`. <br> 2. Os scripts de recebimento de dados devem aplicar validações severas por backend: rejeitar strings de senha fora do intervalo de $[6, 10]$ caracteres e idades fora do intervalo de $[0, 100]$ anos. |
| **RNF-005** | Interoperabilidade | Política Anti-Hotlinking de Imagens | Para contornar bloqueios de exibição de imagens externas (Ex: erro HTTP 403 Forbidden em URLs de terceiros), o HTML gerado deve carregar a tag `<meta name="referrer" content="no-referrer">` no cabeçalho. |
| **RNF-006** | UI / UX | Identidade Visual e Estilo | A interface deve utilizar como base cromática a paleta **Muted Indigo/Purple** (`#4F46E5`, `#4338CA`, `#EEF2F6`). Devem ser implementadas transições suaves de estado (`transition: all 0.3s ease`) e grids de espaçamento baseados em múltiplos de 4px (padding/margin consistentes). |

---

## 3 - Como Acessar e Rodar o Projeto Localmente

Siga os passos abaixo para configurar o ambiente e acessar os arquivos do sistema **BookStorys** na sua máquina:

### 3.1 Clonar o Repositório
Abra o seu terminal ou prompt de comando (CMD) e execute o comando abaixo para clonar o projeto:
```bash
git clone https://github.com/spidemyer/BookStorys.git
```
Depois, entre na pasta do projeto: 

```bash
cd bookstorys
```

### 3.2 Configurar o Banco de Dados (PostgreSQL)

Criar o banco de dados:
```bash
createdb -U postgres bookstorys
```

Importar o arquivo dump.sql para o novo banco

```bash
psql -U postgres -d bookstorys -f dump.sql
```

Depois, abra o arquivo conexao.php no VS Code e certifique-se de que as credenciais de acesso estão assim:

```bash
$host = 'localhost';
$db = 'bookstorys';
$user = 'postgres';
$password = 'postgres';
```

### 3.3 Iniciar o Servidor Local

```bash
php -S localhost:8080 
```

### 3.4 Acessar no Navegador
Com o servidor rodando, abra o seu navegador de preferência e acesse o endereço correspondente:

Área do Cliente (Login/Cadastro): > http://localhost:8080/index.php

Área do Funcionário (Acesso restrito via RF): > http://localhost:8080/login_admin.php

--- 

## 4 - Tecnologias Utilizadas

O projeto **BookStorys** foi desenvolvido utilizando as seguintes tecnologias e ferramentas:

### Back-End e Banco de Dados
* **PHP (Versão 8.5.6):** Utilizado para toda a lógica de negócios, gerenciamento de sessões de usuários (`session_start`), validações de segurança e processamento de requisições.
* **PostgreSQL:** Banco de dados relacional robusto utilizado para persistência dos dados de usuários, funcionários, livros e controle de aluguéis.
* **PDO (PHP Data Objects):** Utilizado para fazer a conexão segura com o banco de dados e execução de *Prepared Statements*, prevenindo ataques de *SQL Injection*.

### Front-End (Interface do Usuário)
* **HTML5:** Estruturação semântica de todas as telas (Vitrine, Logins e Painel Administrativo).
* **CSS3:** Estilização visual customizada baseada em componentes modernos (Cards, Grids, Modais), utilizando variáveis para controle de cores e layouts responsivos.
* **JavaScript (ES6):** Utilizado para interações dinâmicas, como o envio assíncrono de dados via **Fetch API** (para aluguel de livros sem recarregar a página) e manipulação do DOM dos modais.

### Bibliotecas e Recursos Externos
* **Font Awesome (v6.4.0):** Biblioteca de ícones vetoriais utilizada para enriquecer visualmente os botões, campos de input e navegação do sistema.
* **Google Fonts:** Tipografia digital integrada para melhorar a legibilidade da interface.

--- 

## 5. Autoavaliação

### 5.1 O sistema funciona?

O sistema é funcional e atende às restrições arquiteturais propostas.

### 5.2 Consigo explicar o código?

Sim, com o auxílio das explicações no código eu acredito que consigo sim explicar o código.

### 5.3 Me sinto capaz de desenvolver outro sistema parecido?

Sim. Usando esse como base eu acredito que consiga fazer outro sistema, talvez não completamente sozinha.