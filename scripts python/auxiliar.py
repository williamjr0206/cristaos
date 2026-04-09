import pandas as pd
import mysql.connector

# =========================
# CONFIGURAÇÕES
# =========================
arquivo_excel = r''
aba = 'presenças'

config_mysql = {
    'host': '',
    'user': '',
    'password': '',
    'database': ''
}

tabela = 'presencas'

# =========================
# LER PLANILHA
# =========================
df = pd.read_excel(arquivo_excel, sheet_name=aba)

print(df.columns.tolist())