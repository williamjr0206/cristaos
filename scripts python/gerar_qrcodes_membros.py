# Geração Automática de QRCode e Impressão de Etiquetas/Envelopes

import os
import qrcode
import mysql.connector
from mysql.connector import Error

# =====================================
# CONFIGURAÇÃO MYSQL
# =====================================

HOST = 'localhost'
USUARIO = 'root'
SENHA = ''
BANCO = ''

# =====================================
# PASTA DOS QRCODES
# =====================================

PASTA_QRCODES = '../qrcodes'
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PASTA_QRCODES = os.path.join(BASE_DIR, '..', 'qrcodes')

if not os.path.exists(PASTA_QRCODES):
    os.makedirs(PASTA_QRCODES)

# =====================================
# CONEXÃO MYSQL
# =====================================

try:

    conexao = mysql.connector.connect(
        host=HOST,
        user=USUARIO,
        password=SENHA,
        database=BANCO
    )

    cursor = conexao.cursor(dictionary=True)

    sql = """
        SELECT
            id_membro,
            nome_do_membro,
            codigo_barras
        FROM membros
        WHERE status_atual = 'Ativo'
        ORDER BY nome_do_membro
    """

    cursor.execute(sql)

    membros = cursor.fetchall()

    total_gerados = 0
    total_erros = 0

    print('\n===================================')
    print(' GERANDO QRCODES DOS MEMBROS ')
    print('===================================\n')

    for membro in membros:

        try:

            id_membro = membro['id_membro']
            nome = membro['nome_do_membro']
            codigo = membro['codigo_barras']

            if not codigo:
                print(f'[SEM CÓDIGO] {nome}')
                continue

            nome_arquivo = f'{codigo}.png'
            caminho_arquivo = os.path.join(PASTA_QRCODES, nome_arquivo)

            qr = qrcode.QRCode(
                version=1,
                error_correction=qrcode.constants.ERROR_CORRECT_M,
                box_size=10,
                border=4,
            )

            qr.add_data(codigo)
            qr.make(fit=True)

            imagem = qr.make_image(fill_color='black', back_color='white')

            imagem.save(caminho_arquivo)

            total_gerados += 1

            print(f'[OK] {nome} -> {codigo}')

        except Exception as erro_membro:

            total_erros += 1

            print(f'[ERRO] {nome}')
            print(erro_membro)

    print('\n===================================')
    print(' RELATÓRIO FINAL ')
    print('===================================')
    print(f'QRCodes gerados : {total_gerados}')
    print(f'Erros encontrados: {total_erros}')
    print('===================================\n')

except Error as erro:

    print('Erro ao conectar no MySQL')
    print(erro)

finally:

    if 'cursor' in locals():
        cursor.close()

    if 'conexao' in locals() and conexao.is_connected():
        conexao.close()
