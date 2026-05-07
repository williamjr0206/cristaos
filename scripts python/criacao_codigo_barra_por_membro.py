import mysql.connector
from mysql.connector import Error

# ==========================================
# CONFIGURAÇÃO DO BANCO
# ==========================================

HOST = "szjw.com.br"
USUARIO = "szjw_wia"
SENHA = "Wia685618&zenilda"
BANCO = "szjw_cristaos"

# ==========================================
# CONECTAR
# ==========================================

try:

    conexao = mysql.connector.connect(
        host=HOST,
        user=USUARIO,
        password=SENHA,
        database=BANCO
    )

    if conexao.is_connected():

        print("\n====================================")
        print(" CONECTADO AO MYSQL COM SUCESSO ")
        print("====================================\n")

        cursor = conexao.cursor(dictionary=True)

        # ==========================================
        # BUSCAR MEMBROS
        # ==========================================

        sql = """
            SELECT id_membro, nome_do_membro, codigo_barras
            FROM membros
            ORDER BY id_membro
        """

        cursor.execute(sql)

        membros = cursor.fetchall()

        total_atualizados = 0
        total_existentes = 0
        total_erros = 0

        print("GERANDO CÓDIGOS...\n")

        # ==========================================
        # GERAR CÓDIGOS
        # ==========================================

        for membro in membros:

            try:

                id_membro = membro['id_membro']
                nome = membro['nome_do_membro']
                codigo_existente = membro['codigo_barras']

                # Se já possui código
                if codigo_existente and str(codigo_existente).strip() != "":

                    total_existentes += 1

                    print(f"[JÁ EXISTE] {nome} -> {codigo_existente}")

                    continue

                # Gera código novo
                novo_codigo = f"MEM{id_membro:06d}"

                sql_update = """
                    UPDATE membros
                    SET codigo_barras = %s
                    WHERE id_membro = %s
                """

                cursor.execute(sql_update, (novo_codigo, id_membro))

                conexao.commit()

                total_atualizados += 1

                print(f"[CRIADO] {nome} -> {novo_codigo}")

            except Exception as erro_membro:

                total_erros += 1

                print(f"[ERRO] {nome}")
                print(f"Detalhes: {erro_membro}\n")

        # ==========================================
        # RELATÓRIO FINAL
        # ==========================================

        print("\n====================================")
        print(" RELATÓRIO FINAL ")
        print("====================================")

        print(f"Total de membros atualizados : {total_atualizados}")
        print(f"Já possuíam código           : {total_existentes}")
        print(f"Erros encontrados            : {total_erros}")

        print("====================================\n")

except Error as erro:

    print("\nERRO AO CONECTAR NO MYSQL")
    print(erro)

finally:

    if 'cursor' in locals():
        cursor.close()

    if 'conexao' in locals() and conexao.is_connected():
        conexao.close()

        print("Conexão encerrada.")