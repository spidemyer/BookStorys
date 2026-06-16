--
-- PostgreSQL database dump
--

\restrict 5J1nT36XGP9TPrOhGGjem1Kzzhe97QKoIN47OttBl6uthBl3z1xMwM3cVIhpFsb

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: alugueis; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.alugueis (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    livro_id integer NOT NULL,
    data_aluguel timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    data_devolucao_prevista date NOT NULL,
    status character varying(20) DEFAULT 'Pendente'::character varying NOT NULL
);


ALTER TABLE public.alugueis OWNER TO postgres;

--
-- Name: alugueis_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.alugueis_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.alugueis_id_seq OWNER TO postgres;

--
-- Name: alugueis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.alugueis_id_seq OWNED BY public.alugueis.id;


--
-- Name: funcionarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.funcionarios (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    rf character varying(20) NOT NULL
);


ALTER TABLE public.funcionarios OWNER TO postgres;

--
-- Name: funcionarios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.funcionarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.funcionarios_id_seq OWNER TO postgres;

--
-- Name: funcionarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.funcionarios_id_seq OWNED BY public.funcionarios.id;


--
-- Name: livros; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.livros (
    id integer NOT NULL,
    titulo character varying(150) NOT NULL,
    autor character varying(100) NOT NULL,
    estoque integer DEFAULT 0 NOT NULL,
    url_capa text NOT NULL,
    funcionario_rf character varying(20) NOT NULL
);


ALTER TABLE public.livros OWNER TO postgres;

--
-- Name: livros_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.livros_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.livros_id_seq OWNER TO postgres;

--
-- Name: livros_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.livros_id_seq OWNED BY public.livros.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuarios (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    idade integer NOT NULL,
    email character varying(100) NOT NULL,
    senha character varying(255) NOT NULL
);


ALTER TABLE public.usuarios OWNER TO postgres;

--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_id_seq OWNER TO postgres;

--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- Name: alugueis id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alugueis ALTER COLUMN id SET DEFAULT nextval('public.alugueis_id_seq'::regclass);


--
-- Name: funcionarios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.funcionarios ALTER COLUMN id SET DEFAULT nextval('public.funcionarios_id_seq'::regclass);


--
-- Name: livros id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.livros ALTER COLUMN id SET DEFAULT nextval('public.livros_id_seq'::regclass);


--
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- Data for Name: alugueis; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.alugueis (id, usuario_id, livro_id, data_aluguel, data_devolucao_prevista, status) FROM stdin;
1	1	3	2026-06-09 14:29:24.602912	2026-06-16	Devolvido
2	1	4	2026-06-09 14:38:27.123421	2026-06-16	Devolvido
3	1	4	2026-06-09 14:38:29.343128	2026-06-18	Devolvido
6	4	1	2026-06-09 15:09:10.155124	2026-06-05	Devolvido
7	3	8	2026-06-09 15:15:52.789363	2026-06-16	Pendente
5	3	6	2026-06-09 15:03:07.499009	2026-06-02	Devolvido
8	5	3	2026-06-11 08:41:56.590426	2026-06-18	Pendente
4	1	4	2026-06-09 15:01:55.606833	2026-06-09	Devolvido
9	7	3	2026-06-11 00:00:00	2026-06-18	Devolvido
10	8	3	2026-06-16 00:00:00	2026-06-23	Pendente
\.


--
-- Data for Name: funcionarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.funcionarios (id, nome, rf) FROM stdin;
1	admin	123456
2	Gabriela	130101
3	Lucas	4324
4	Lila	2224
5	Emily	1301
\.


--
-- Data for Name: livros; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.livros (id, titulo, autor, estoque, url_capa, funcionario_rf) FROM stdin;
2	Harry Potter e a Pedra Filosofal	J.K. Rowling	5	img/harry-potter.jpg	123456
9	Percy Jackson e o ladrão de raios	Rick Riordan	2	img/1781028375_book_6a2856175d48f5.84674324.jpg	4324
1	O Senhor dos Anéis	J.R.R. Tolkien	3	img/senhor-dos-aneis.jpg	123456
10	Jujutsu Kaisen V.04	Gege Akutami	10	img/1781028735_book_6a28577f0c06e3.31527452.jpg	123456
8	Vidas Secas	Graciliano Ramos	3	img/1781024807_book_6a284827a8fea5.73725592.jpg	123456
6	O Pequeno Principe	Antoine de Saint-Exupéry	3	img/o-pequeno-principe.jpg	130101
4	Dom Casmurro	Machado de Assis	2	img/1781024741_update_6a2847e5783c1.jpg	123456
3	1984	George Orwell	2	img/1984.jpg	123456
\.


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuarios (id, nome, idade, email, senha) FROM stdin;
1	EMILY GUIMARAES PEREIRA	17	emilymima.jessica@gmail.com	$2y$12$xsfFPbZt7avRYYEZD.nMGuwXqpohykeXoNWvaX780BINUWFtAKim2
2	gabriela	16	gabi@gmail.com	$2y$12$aYY6oN5sBARRMuGVi1HCAO/14DH/3mOZX2P/XARKxcHrCtqe4yKyW
3	Evelyn Levindo	17	evelyn@gmail.com	$2y$12$33WV/p3TFPhE7qCNo196feMUdSrlFq6szVcqBBIsd6/3KzJ6PhMQu
4	Davi Martins	18	davibrennam2008@gmail.com	$2y$12$T9cuydSXYQhuW6Sb9KIKmu0q451nplGBGhzcojordA8Z3/YnwDim6
5	JJ	-4	j@j.com.br	$2y$12$6VfloOOdIhmCWcVFwBp3EOjEOYPcyxo37U3m7jtW2lw9Mw39xKXKe
6	tata	16	tata@gmail.com	$2y$12$eQ1UeKJVK33ueyxidYgBCeyrYG2Hwf2gCXLaB4F8myXIhxB730ptm
7	Jessica	34	jeh@gmail.com	$2y$12$UxDFSaB7oInoXmJRCbgU9.1vSFyR8OMLdAd2s5wtVP8ETFyS2F5tO
8	Daniel de Oliveira Cabral	99	daniel@email.com	$2y$12$RobiYlj3PWveWKNAVH8PlexPnSAS2cM1MkQ0ilgmyPzSzR.zopxVS
\.


--
-- Name: alugueis_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.alugueis_id_seq', 10, true);


--
-- Name: funcionarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.funcionarios_id_seq', 5, true);


--
-- Name: livros_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.livros_id_seq', 10, true);


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 8, true);


--
-- Name: alugueis alugueis_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alugueis
    ADD CONSTRAINT alugueis_pkey PRIMARY KEY (id);


--
-- Name: funcionarios funcionarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.funcionarios
    ADD CONSTRAINT funcionarios_pkey PRIMARY KEY (id);


--
-- Name: funcionarios funcionarios_rf_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.funcionarios
    ADD CONSTRAINT funcionarios_rf_key UNIQUE (rf);


--
-- Name: livros livros_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.livros
    ADD CONSTRAINT livros_pkey PRIMARY KEY (id);


--
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: livros fk_funcionario_livro; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.livros
    ADD CONSTRAINT fk_funcionario_livro FOREIGN KEY (funcionario_rf) REFERENCES public.funcionarios(rf) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: alugueis fk_livro_aluguel; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alugueis
    ADD CONSTRAINT fk_livro_aluguel FOREIGN KEY (livro_id) REFERENCES public.livros(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: alugueis fk_usuario_aluguel; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alugueis
    ADD CONSTRAINT fk_usuario_aluguel FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

\unrestrict 5J1nT36XGP9TPrOhGGjem1Kzzhe97QKoIN47OttBl6uthBl3z1xMwM3cVIhpFsb

