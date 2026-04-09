import re
import pandas as pd
import mysql.connector
from decimal import Decimal
from mysql.connector import Error

# =========================================
# CONFIGURAÇÕES
# =========================================

ARQUIVO_EXCEL = r''   # ajuste aqui
ABA_EXCEL = "membros"

MYSQL_CONFIG = {
    "host": "",
    "user": "",
    "password": "",
    "database": ""
}

TABELA_MYSQL = "membros"

COLUNA_NOME = "nome_do_membro"
COLUNA_TELEFONE = "telefone"

TELEFONE_ERRADO = "2147483647"

# =========================================
# FUNÇÕES
# =========================================

def limpar_nome(nome):
    if pd.isna(nome):
        return ""
    return str(nome).strip()

def normalizar_valor_excel(valor):
    """
    Converte qualquer valor vindo do Excel para string utilizável,
    sem deixar notação científica atrapalhar.
    """
    if pd.isna(valor):
        return ""

    texto = str(valor).strip()

    # remove .0 no final, muito comum quando veio como número
    if texto.endswith(".0"):
        texto = texto[:-2]

    return texto

def limpar_telefone(telefone):
    """
    Trata telefone vindo do Excel como texto.
    Aceita:
    - 10 dígitos
    - 11 dígitos
    - 12/13 dígitos com 55 na frente
    """
    telefone = normalizar_valor_excel(telefone)

    if telefone == "":
        return None

    # remove tudo que não for dígito
    tel = re.sub(r"\D", "", telefone)

    if tel == "":
        return None

    # remove DDI Brasil se vier junto
    if tel.startswith("55") and len(tel) in (12, 13):
        tel = tel[2:]

    # aceita fixo com DDD ou celular com DDD
    if len(tel) in (10, 11):
        return tel

    return None

# =========================================
# LEITURA DA PLANILHA
# =========================================

try:
    df = pd.read_excel(
        ARQUIVO_EXCEL,
        sheet_name=ABA_EXCEL,
        dtype=str
    )
except Exception as e:
    print(f"Erro ao abrir a planilha: {e}")
    raise

for col in [COLUNA_NOME, COLUNA_TELEFONE]:
    if col not in df.columns:
        raise ValueError(f"Coluna '{col}' não encontrada na aba '{ABA_EXCEL}'.")

df = df[[COLUNA_NOME, COLUNA_TELEFONE]].copy()

df[COLUNA_NOME] = df[COLUNA_NOME].apply(limpar_nome)
df[COLUNA_TELEFONE] = df[COLUNA_TELEFONE].apply(normalizar_valor_excel)
df["telefone_limpo"] = df[COLUNA_TELEFONE].apply(limpar_telefone)

df = df[df[COLUNA_NOME] != ""]
df = df.drop_duplicates(subset=[COLUNA_NOME], keep="last")

telefones_excel = {
    row[COLUNA_NOME]: row["telefone_limpo"]
    for _, row in df.iterrows()
}

# =========================================
# ATUALIZAÇÃO NO MYSQL
# =========================================

corrigidos = []
sem_telefone_na_planilha = []
invalidos_na_planilha = []
nao_encontrados_na_planilha = []
erros = []

try:
    conn = mysql.connector.connect(**MYSQL_CONFIG)
    cursor = conn.cursor(dictionary=True)

    sql_busca = f"""
        SELECT nome_do_membro, telefone
        FROM {TABELA_MYSQL}
        WHERE telefone = %s
    """
    cursor.execute(sql_busca, (TELEFONE_ERRADO,))
    membros_errados = cursor.fetchall()

    sql_update = f"""
        UPDATE {TABELA_MYSQL}
        SET telefone = %s
        WHERE nome_do_membro = %s
          AND telefone = %s
    """

    for membro in membros_errados:
        nome = str(membro["nome_do_membro"]).strip()
        telefone_atual = str(membro["telefone"]).strip()

        if nome not in telefones_excel:
            nao_encontrados_na_planilha.append((nome, telefone_atual))
            continue

        telefone_correto = telefones_excel[nome]

        valor_original_excel = df.loc[df[COLUNA_NOME] == nome, COLUNA_TELEFONE].iloc[0]

        if valor_original_excel == "":
            sem_telefone_na_planilha.append((nome, valor_original_excel))
            continue

        if telefone_correto is None:
            invalidos_na_planilha.append((nome, valor_original_excel))
            continue

        try:
            cursor.execute(sql_update, (telefone_correto, nome, TELEFONE_ERRADO))
            corrigidos.append((nome, telefone_atual, telefone_correto))
        except Exception as e:
            erros.append((nome, telefone_atual, str(e)))

    conn.commit()

except Error as e:
    print(f"Erro no MySQL: {e}")
    raise

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()

# =========================================
# RELATÓRIO FINAL
# =========================================

print("\n===== CORREÇÃO DE TELEFONES =====")
print(f"Corrigidos com sucesso: {len(corrigidos)}")
print(f"Sem telefone na planilha: {len(sem_telefone_na_planilha)}")
print(f"Telefone inválido na planilha: {len(invalidos_na_planilha)}")
print(f"Não encontrados na planilha: {len(nao_encontrados_na_planilha)}")
print(f"Erros: {len(erros)}")

if corrigidos:
    print("\n--- CORRIGIDOS ---")
    for nome, antigo, novo in corrigidos[:50]:
        print(f"{nome} | banco: {antigo} | novo: {novo}")

if sem_telefone_na_planilha:
    print("\n--- SEM TELEFONE NA PLANILHA ---")
    for nome, valor in sem_telefone_na_planilha[:50]:
        print(f"{nome} | planilha: {valor}")

if invalidos_na_planilha:
    print("\n--- TELEFONE INVÁLIDO NA PLANILHA ---")
    for nome, valor in invalidos_na_planilha[:50]:
        print(f"{nome} | planilha: {valor}")

if nao_encontrados_na_planilha:
    print("\n--- NÃO ENCONTRADOS NA PLANILHA ---")
    for nome, valor in nao_encontrados_na_planilha[:50]:
        print(f"{nome} | banco: {valor}")

if erros:
    print("\n--- ERROS ---")
    for nome, valor, erro in erros[:50]:
        print(f"{nome} | banco: {valor} | erro: {erro}")