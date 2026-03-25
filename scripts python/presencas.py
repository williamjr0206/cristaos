import pandas as pd
import mysql.connector

# =========================
# CONFIGURAÇÕES
# =========================
arquivo_excel = r'C:\Users\William\OneDrive\IPI - Muzambinho\ebd.xlsx'
aba = 'presenças'

config_mysql = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'szjw_cristaos'
}

tabela = 'presencas'

# =========================
# LER PLANILHA
# =========================
df = pd.read_excel(arquivo_excel, sheet_name=aba)

# =========================
# LIMPEZA DE COLUNAS
# =========================

# Remover colunas sem nome
df = df.loc[:, df.columns.notna()]

# Remover espaços dos nomes
df.columns = df.columns.str.strip()

# Remover colunas ocultas (pelas letras)
colunas_remover = ['C', 'D', 'E','F', 'G', 'I', 'K', 'L']
indices_remover = [ord(col) - ord('A') for col in colunas_remover]

# Só remove se existir (evita erro)
indices_validos = [i for i in indices_remover if i < len(df.columns)]
df = df.drop(df.columns[indices_validos], axis=1)

# =========================
# TRATAR DADOS
# =========================

# Converter NaN -> None (MySQL aceita como NULL)
df = df.where(pd.notnull(df), None)

# Converter datas (se existir coluna 'data')
if 'data' in df.columns:
    df['data'] = pd.to_datetime(df['data'], errors='coerce')

# =========================
# CONECTAR MYSQL
# =========================
conn = mysql.connector.connect(**config_mysql)
cursor = conn.cursor()

# =========================
# MONTAR QUERY
# =========================
colunas = df.columns.tolist()

placeholders = ', '.join(['%s'] * len(colunas))
colunas_sql = ', '.join(colunas)

for col in df.columns:
    if df[col].astype(str).str.contains('nan', case=False).any():
        print(f"⚠️ Ainda tem 'nan' na coluna: {col}")

sql = f"INSERT INTO {tabela} ({colunas_sql}) VALUES ({placeholders})"

# =========================
# INSERIR DADOS (COM TRATAMENTO)
# =========================
sucesso = 0
erros = 0

for index, row in df.iterrows():
    try:
        valores = tuple(row)
        cursor.execute(sql, valores)
        sucesso += 1
    except Exception as e:
        print(f"Erro na linha {index}: {e}")
        erros += 1

# Commit final
conn.commit()

# =========================
# RESULTADO
# =========================
print(f"\n✅ Inseridos com sucesso: {sucesso}")
print(f"❌ Erros: {erros}")

# =========================
# FINALIZAR
# =========================
cursor.close()
conn.close()