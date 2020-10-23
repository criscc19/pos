insert into llx_c_paiement (id,code,libelle,type,active) values (100, 'PNT', 'Conversión Puntos', 2,1);
ALTER TABLE llx_rewards MODIFY COLUMN points double(24,8) DEFAULT 0;
ALTER TABLE llx_rewards ADD date DATE NULL DEFAULT NULL;
ALTER TABLE llx_facture_cashdespro ADD fk_mesa INT(11) NULL AFTER act_eco;
ALTER TABLE llx_facturedet_cashdespro ADD estado INT NOT NULL DEFAULT '0' AFTER multicurrency_tx;