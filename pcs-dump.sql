--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.6
-- Dumped by pg_dump version 9.1.5
-- Started on 2012-11-29 08:00:09 PST

SET statement_timeout = 0;
SET client_encoding = 'SQL_ASCII';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

ALTER TABLE ONLY public.printer DROP CONSTRAINT printer_model_fkey;
ALTER TABLE ONLY public.pc DROP CONSTRAINT pc_model_fkey;
ALTER TABLE ONLY public.laptop DROP CONSTRAINT laptop_model_fkey;
ALTER TABLE ONLY public.ships DROP CONSTRAINT ships_pkey;
ALTER TABLE ONLY public.product DROP CONSTRAINT product_pkey;
ALTER TABLE ONLY public.printer DROP CONSTRAINT printer_pkey;
ALTER TABLE ONLY public.pc DROP CONSTRAINT pc_pkey;
ALTER TABLE ONLY public.outcomes DROP CONSTRAINT outcomes_pkey;
ALTER TABLE ONLY public.laptop DROP CONSTRAINT laptop_pkey;
ALTER TABLE ONLY public.classes DROP CONSTRAINT classes_pkey;
ALTER TABLE ONLY public.battles DROP CONSTRAINT battles_pkey;
DROP TABLE public.ships;
DROP TABLE public.product;
DROP TABLE public.printer;
DROP TABLE public.pc;
DROP TABLE public.outcomes;
DROP TABLE public.laptop;
DROP TABLE public.classes;
DROP TABLE public.battles;
DROP EXTENSION plpgsql;
DROP SCHEMA public;
--
-- TOC entry 6 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO postgres;

--
-- TOC entry 1944 (class 0 OID 0)
-- Dependencies: 6
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'standard public schema';


--
-- TOC entry 169 (class 3079 OID 11678)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 1945 (class 0 OID 0)
-- Dependencies: 169
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 167 (class 1259 OID 27850)
-- Dependencies: 6
-- Name: laptop; Type: TABLE; Schema: public; Owner: class55; Tablespace: 
--

CREATE TABLE laptop (
    model integer NOT NULL,
    speed numeric(5,2),
    ram integer,
    hd integer,
    screen numeric(3,1),
    price integer
);

--
-- TOC entry 166 (class 1259 OID 27830)
-- Dependencies: 6
-- Name: pc; Type: TABLE; Schema: public; Owner: class55; Tablespace: 
--

CREATE TABLE pc (
    model integer NOT NULL,
    speed numeric(5,2),
    ram integer,
    hd integer,
    price integer
);

--
-- TOC entry 168 (class 1259 OID 27860)
-- Dependencies: 6
-- Name: printer; Type: TABLE; Schema: public; Owner: class55; Tablespace: 
--

CREATE TABLE printer (
    model integer NOT NULL,
    color boolean,
    type character varying(20),
    price integer
);

--
-- TOC entry 165 (class 1259 OID 27825)
-- Dependencies: 6
-- Name: product; Type: TABLE; Schema: public; Owner: class55; Tablespace: 
--

CREATE TABLE product (
    model integer NOT NULL,
    type character varying(20),
    maker character varying(20)
);

--
-- TOC entry 1937 (class 0 OID 27850)
-- Dependencies: 167 1939
-- Data for Name: laptop; Type: TABLE DATA; Schema: public; Owner: class55
--

COPY laptop (model, speed, ram, hd, screen, price) FROM stdin;
2001	2.00	2048	240	20.1	3673
2002	1.73	1024	80	17.0	949
2003	1.80	512	60	15.4	549
2004	2.00	512	60	13.3	1150
2005	2.16	1024	120	17.0	2500
2006	2.00	2048	80	15.4	1700
2007	1.83	1024	100	13.3	1429
2008	1.60	1024	120	15.4	900
2009	1.60	512	80	14.1	680
2010	2.00	2048	160	15.4	2300
\.


--
-- TOC entry 1936 (class 0 OID 27830)
-- Dependencies: 166 1939
-- Data for Name: pc; Type: TABLE DATA; Schema: public; Owner: class55
--

COPY pc (model, speed, ram, hd, price) FROM stdin;
1002	2.10	512	250	995
1003	1.42	512	80	478
1004	2.80	1024	250	649
1005	3.20	512	250	630
1006	3.20	1024	320	1049
1007	2.20	1024	200	510
1008	2.20	2048	250	770
1009	2.00	1024	250	650
1010	2.80	2048	300	770
1011	1.86	2048	160	959
1012	2.80	1024	160	649
1013	3.06	512	80	529
1014	3.20	1024	250	600
1001	2.66	1024	250	2114
\.


--
-- TOC entry 1938 (class 0 OID 27860)
-- Dependencies: 168 1939
-- Data for Name: printer; Type: TABLE DATA; Schema: public; Owner: class55
--

COPY printer (model, color, type, price) FROM stdin;
3001	t	ink-jet	99
3002	f	laser	239
3003	t	laser	899
3004	t	ink-jet	120
3005	f	laser	120
3006	t	ink-jet	100
3007	t	laser	200
\.


--
-- TOC entry 1935 (class 0 OID 27825)
-- Dependencies: 165 1939
-- Data for Name: product; Type: TABLE DATA; Schema: public; Owner: class55
--

COPY product (model, type, maker) FROM stdin;
1001	pc	A
1002	pc	A
1003	pc	A
2004	laptop	A
2005	laptop	A
2006	laptop	A
1004	pc	B
1005	pc	B
1006	pc	B
2007	laptop	B
1007	pc	C
1008	pc	D
1009	pc	D
1010	pc	D
3004	printer	D
3005	printer	D
1011	pc	E
1012	pc	E
1013	pc	E
2001	laptop	E
2002	laptop	E
2003	laptop	E
3001	printer	E
3002	printer	E
3003	printer	E
2008	laptop	F
2009	laptop	F
2010	laptop	G
3006	printer	H
3007	printer	H
1014	pc	A
\.


--
-- TOC entry 1925 (class 2606 OID 27854)
-- Dependencies: 167 167 1940
-- Name: laptop_pkey; Type: CONSTRAINT; Schema: public; Owner: class55; Tablespace: 
--

ALTER TABLE ONLY laptop
    ADD CONSTRAINT laptop_pkey PRIMARY KEY (model);


--
-- TOC entry 1923 (class 2606 OID 27834)
-- Dependencies: 166 166 1940
-- Name: pc_pkey; Type: CONSTRAINT; Schema: public; Owner: class55; Tablespace: 
--

ALTER TABLE ONLY pc
    ADD CONSTRAINT pc_pkey PRIMARY KEY (model);


--
-- TOC entry 1927 (class 2606 OID 27864)
-- Dependencies: 168 168 1940
-- Name: printer_pkey; Type: CONSTRAINT; Schema: public; Owner: class55; Tablespace: 
--

ALTER TABLE ONLY printer
    ADD CONSTRAINT printer_pkey PRIMARY KEY (model);


--
-- TOC entry 1921 (class 2606 OID 27829)
-- Dependencies: 165 165 1940
-- Name: product_pkey; Type: CONSTRAINT; Schema: public; Owner: class55; Tablespace: 
--

ALTER TABLE ONLY product
    ADD CONSTRAINT product_pkey PRIMARY KEY (model);


--
-- TOC entry 1929 (class 2606 OID 27855)
-- Dependencies: 165 167 1920 1940
-- Name: laptop_model_fkey; Type: FK CONSTRAINT; Schema: public; Owner: class55
--

ALTER TABLE ONLY laptop
    ADD CONSTRAINT laptop_model_fkey FOREIGN KEY (model) REFERENCES product(model);


--
-- TOC entry 1928 (class 2606 OID 27835)
-- Dependencies: 1920 166 165 1940
-- Name: pc_model_fkey; Type: FK CONSTRAINT; Schema: public; Owner: class55
--

ALTER TABLE ONLY pc
    ADD CONSTRAINT pc_model_fkey FOREIGN KEY (model) REFERENCES product(model);


--
-- TOC entry 1930 (class 2606 OID 27865)
-- Dependencies: 1920 168 165 1940
-- Name: printer_model_fkey; Type: FK CONSTRAINT; Schema: public; Owner: class55
--

ALTER TABLE ONLY printer
    ADD CONSTRAINT printer_model_fkey FOREIGN KEY (model) REFERENCES product(model);


-- Completed on 2012-11-29 08:00:15 PST

--
-- PostgreSQL database dump complete
--

