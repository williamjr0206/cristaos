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

print(df.columns.tolist())