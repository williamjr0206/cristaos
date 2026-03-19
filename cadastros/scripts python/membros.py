import pandas as pd
import mysql.connector
import numpy as np

# =========================
# CONFIG
# =========================
arquivo_excel = r'C:\Users\William\OneDrive\IPI - Muzambinho\ebd.xlsx'
aba = 'membros'
tabela = 'membros'

conexao = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='szjw_cristaos'
)

cursor = conexao.cursor()

# =========================
# LER EXCEL
# =========================
df = pd.read_excel(arquivo_excel, sheet_name=aba)

df.columns = df.columns.str.strip()

# =========================
# AJUSTAR NOMES DAS COLUNAS
# =========================
df = df.rename(columns={
    'nome': 'nome_do_membro',
    'email': 'e-mail'
})

# =========================
# REMOVER ID AUTO_INCREMENT
# =========================
if 'id_membro' in df.columns:
    df = df.drop(columns=['id_membro'])

# =========================
# TRATAR VALORES NULOS
# =========================
df = df.replace({np.nan: None})

# =========================
# GARANTIR CAMPOS OBRIGATÓRIOS
# =========================
df['id_igreja'] = df['id_igreja'].fillna(1)
df['id_tipo'] = df['id_tipo'].fillna(1)
df['id_cargo'] = df['id_cargo'].fillna(1)

# =========================
# MONTAR INSERT (COM BACKTICKS)
# =========================
colunas = ', '.join([f"`{col}`" for col in df.columns])
placeholders = ', '.join(['%s'] * len(df.columns))

sql = f"INSERT INTO {tabela} ({colunas}) VALUES ({placeholders})"

# =========================
# INSERÇÃO RÁPIDA
# =========================
dados = [tuple(linha) for linha in df.to_numpy()]

cursor.executemany(sql, dados)

conexao.commit()

cursor.close()
conexao.close()

print("Dados importados com sucesso!")