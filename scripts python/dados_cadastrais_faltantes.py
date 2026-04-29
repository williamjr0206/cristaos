from __future__ import annotations

import os
from dataclasses import dataclass
from datetime import date, datetime
from typing import Any, Dict, List, Tuple

import mysql.connector
from mysql.connector import Error
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import mm
from reportlab.platypus import (
    SimpleDocTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
    PageBreak,
    KeepTogether,
)


# ============================================================
# CONFIGURACAO
# ============================================================
DB_CONFIG = {
    "host": os.getenv("MYSQL_HOST", "szjw.com.br"),
    "port": int(os.getenv("MYSQL_PORT", "3306")),
    "user": os.getenv("MYSQL_USER", "szjw_wia"),
    "password": os.getenv("MYSQL_PASSWORD", "Wia685618&zenilda"),
    "database": os.getenv("MYSQL_DATABASE", "szjw_cristaos"),
}

OUTPUT_PDF = os.getenv("OUTPUT_PDF", "relatorio_dados_cadastrais_faltantes.pdf")

# Considere vazio tanto NULL quanto string vazia/espacos.
CAMPOS_ANALISADOS = [
    "id_igreja",
    "nome_do_membro",
    "id_tipo",
    "telefone",
    "sexo",
    "data_nascimento",
    "nacionalidade",
    "naturalidade",
    "nome_do_pai",
    "nome_da_mae",
    "tipo_sanguineo",
    "estado_civil",
    "cep",
    "endereco",
    "cidade",
    "estado",
    "email",
    "ativo",
    "data_batismo",
    "data_profissao_de_fe",
    "id_cargo",
    "data_cadastro",
]

# Campos do layout conforme o formulario-modelo enviado.
SECOES = {
    "1. Dados pessoais": [
        "nome_do_membro",
        "nome_do_pai",
        "nome_da_mae",
        "data_nascimento",
        "naturalidade",
        "nacionalidade",
        "sexo",
        "estado_civil",
        "tipo_sanguineo",
        "data_batismo",
        "data_profissao_de_fe",
    ],
    "2. Contato e endereco": [
        "telefone",
        "email",
        "endereco",
        "cidade",
        "estado",
        "cep",
    ],
    "4. Uso exclusivo da secretaria": [
        "data_cadastro",
        "ativo",
        "id_igreja",
        "id_tipo",
        "id_cargo",
    ],
}

ROTULOS = {
    "id_membro": "ID do membro",
    "id_igreja": "ID Igreja",
    "nome_do_membro": "Nome do membro",
    "id_tipo": "ID Tipo",
    "telefone": "Telefone",
    "sexo": "Sexo",
    "data_nascimento": "Data de nascimento",
    "nacionalidade": "Nacionalidade",
    "naturalidade": "Naturalidade",
    "nome_do_pai": "Nome do pai",
    "nome_da_mae": "Nome da mae",
    "tipo_sanguineo": "Tipo sanguineo",
    "estado_civil": "Estado civil",
    "cep": "CEP",
    "endereco": "Endereco",
    "cidade": "Cidade",
    "estado": "Estado",
    "email": "E-mail",
    "ativo": "Situacao (ativo)",
    "data_batismo": "Data do batismo",
    "data_profissao_de_fe": "Data da profissao de fe",
    "id_cargo": "Cargo / funcao",
    "data_cadastro": "Data do cadastro",
}


@dataclass
class Membro:
    dados: Dict[str, Any]
    faltantes: List[str]


# ============================================================
# UTILITARIOS
# ============================================================
def valor_vazio(valor: Any, campo: str) -> bool:
    if campo == "telefone":
        if valor is None:
            return True
        texto = str(valor).strip()
        return texto == "" or texto == "1234"

    if valor is None:
        return True

    if isinstance(valor, str):
        return valor.strip() == ""

    return False



def formatar_data(valor: Any) -> str:
    if valor is None or valor == "":
        return ""
    if isinstance(valor, datetime):
        return valor.strftime("%d/%m/%Y %H:%M:%S")
    if isinstance(valor, date):
        return valor.strftime("%d/%m/%Y")
    texto = str(valor).strip()
    if not texto:
        return ""
    for fmt in ("%Y-%m-%d", "%Y-%m-%d %H:%M:%S"):
        try:
            dt = datetime.strptime(texto, fmt)
            return dt.strftime("%d/%m/%Y") if fmt == "%Y-%m-%d" else dt.strftime("%d/%m/%Y %H:%M:%S")
        except ValueError:
            pass
    return texto



def formatar_telefone(valor: Any) -> str:
    if valor is None:
        return ""
    fone = "".join(ch for ch in str(valor) if ch.isdigit())
    if fone == "1234":
        return ""
    if len(fone) == 11:
        return f"({fone[:2]}) {fone[2:7]}-{fone[7:]}"
    if len(fone) == 10:
        return f"({fone[:2]}) {fone[2:6]}-{fone[6:]}"
    return str(valor).strip()



def formatar_cep(valor: Any) -> str:
    if valor is None or str(valor).strip() == "":
        return ""
    cep = "".join(ch for ch in str(valor) if ch.isdigit())
    cep = cep.zfill(8)
    if len(cep) == 8:
        return f"{cep[:5]}-{cep[5:]}"
    return str(valor).strip()



def formatar_valor(campo: str, valor: Any) -> str:
    if valor_vazio(valor, campo):
        return ""
    if campo in {"data_nascimento", "data_batismo", "data_profissao_de_fe", "data_cadastro"}:
        return formatar_data(valor)
    if campo == "telefone":
        return formatar_telefone(valor)
    if campo == "cep":
        return formatar_cep(valor)
    if campo == "ativo":
        return "Ativo" if int(valor) == 1 else "Inativo"
    return str(valor).strip()



def linha_vazia(tamanho: int = 40) -> str:
    return "_" * tamanho


# ============================================================
# BANCO DE DADOS
# ============================================================
def conectar_mysql():
    return mysql.connector.connect(**DB_CONFIG)



def montar_where_faltantes(campos: List[str]) -> str:
    condicoes = []
    for campo in campos:
        if campo == "telefone":
            condicoes.append("telefone IS NULL")
            condicoes.append("TRIM(CAST(telefone AS CHAR)) = ''")
            condicoes.append("CAST(telefone AS CHAR) = '1234'")
        elif campo in {"id_igreja", "id_tipo", "ativo", "id_cargo", "data_nascimento", "data_batismo", "data_profissao_de_fe", "data_cadastro", "cep"}:
            condicoes.append(f"{campo} IS NULL")
        else:
            condicoes.append(f"{campo} IS NULL")
            condicoes.append(f"TRIM({campo}) = ''")
    return " OR ".join(condicoes)



def buscar_membros_com_faltas() -> List[Membro]:
    conexao = conectar_mysql()
    try:
        cursor = conexao.cursor(dictionary=True)
        campos_select = ["id_membro"] + CAMPOS_ANALISADOS
        sql = f"""
            SELECT {', '.join(campos_select)}
            FROM membros
            WHERE {montar_where_faltantes(CAMPOS_ANALISADOS)}
            ORDER BY nome_do_membro ASC
        """
        cursor.execute(sql)
        registros = cursor.fetchall()

        membros: List[Membro] = []
        for registro in registros:
            faltantes = [campo for campo in CAMPOS_ANALISADOS if valor_vazio(registro.get(campo), campo)]
            membros.append(Membro(dados=registro, faltantes=faltantes))
        return membros
    finally:
        try:
            cursor.close()
        except Exception:
            pass
        conexao.close()


# ============================================================
# PDF
# ============================================================
def estilos_pdf():
    estilos_base = getSampleStyleSheet()
    return {
        "titulo": ParagraphStyle(
            "Titulo",
            parent=estilos_base["Title"],
            fontName="Helvetica-Bold",
            fontSize=16,
            leading=20,
            textColor=colors.HexColor("#163A63"),
            spaceAfter=8,
        ),
        "subtitulo": ParagraphStyle(
            "Subtitulo",
            parent=estilos_base["Normal"],
            fontName="Helvetica",
            fontSize=9,
            leading=12,
            textColor=colors.HexColor("#4A5568"),
            spaceAfter=8,
        ),
        "secao": ParagraphStyle(
            "Secao",
            parent=estilos_base["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=11,
            leading=13,
            textColor=colors.white,
            backColor=colors.HexColor("#163A63"),
            leftIndent=4,
            spaceBefore=6,
            spaceAfter=6,
        ),
        "normal": ParagraphStyle(
            "NormalCustom",
            parent=estilos_base["Normal"],
            fontName="Helvetica",
            fontSize=9,
            leading=12,
            textColor=colors.HexColor("#222222"),
        ),
        "pequeno": ParagraphStyle(
            "Pequeno",
            parent=estilos_base["Normal"],
            fontName="Helvetica",
            fontSize=8,
            leading=10,
            textColor=colors.HexColor("#555555"),
        ),
        "rotulo": ParagraphStyle(
            "Rotulo",
            parent=estilos_base["Normal"],
            fontName="Helvetica-Bold",
            fontSize=8.5,
            leading=10,
            textColor=colors.HexColor("#334155"),
        ),
        "valor": ParagraphStyle(
            "Valor",
            parent=estilos_base["Normal"],
            fontName="Helvetica",
            fontSize=9,
            leading=11,
            textColor=colors.HexColor("#111827"),
        ),
    }



def par_rotulo_valor(campo: str, valor: str, estilos: Dict[str, ParagraphStyle], faltante: bool) -> List[Any]:
    rotulo = Paragraph(ROTULOS.get(campo, campo), estilos["rotulo"])
    texto_valor = valor if valor else linha_vazia(48)
    if faltante:
        texto_valor += " <font color='#B91C1C'>(preencher)</font>"
    valor_p = Paragraph(texto_valor.replace("\n", "<br/>"), estilos["valor"])
    return [rotulo, Spacer(1, 1.5 * mm), valor_p]



def tabela_campos(membro: Membro, campos: List[str], estilos: Dict[str, ParagraphStyle]) -> Table:
    linhas = []
    for campo in campos:
        valor = formatar_valor(campo, membro.dados.get(campo))
        faltante = campo in membro.faltantes
        conteudo = par_rotulo_valor(campo, valor, estilos, faltante)
        linhas.append([conteudo])

    tabela = Table(linhas, colWidths=[180 * mm])
    tabela.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, -1), colors.HexColor("#F8FAFC")),
                ("BOX", (0, 0), (-1, -1), 0.5, colors.HexColor("#CBD5E1")),
                ("INNERGRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#E2E8F0")),
                ("LEFTPADDING", (0, 0), (-1, -1), 7),
                ("RIGHTPADDING", (0, 0), (-1, -1), 7),
                ("TOPPADDING", (0, 0), (-1, -1), 6),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
            ]
        )
    )
    return tabela



def gerar_pdf(membros: List[Membro], arquivo_saida: str) -> None:
    estilos = estilos_pdf()
    doc = SimpleDocTemplate(
        arquivo_saida,
        pagesize=A4,
        leftMargin=15 * mm,
        rightMargin=15 * mm,
        topMargin=14 * mm,
        bottomMargin=12 * mm,
        title="Relatorio de Dados Cadastrais Faltantes",
        author="dados_cadastrais_faltantes.py",
    )

    historia: List[Any] = []

    historia.append(Paragraph("IGREJA PRESBITERIANA INDEPENDENTE DE MUZAMBINHO", estilos["titulo"]))
    historia.append(Paragraph("Relatorio de dados cadastrais faltantes - membros", estilos["subtitulo"]))
    historia.append(
        Paragraph(
            f"Foram encontrados <b>{len(membros)}</b> membro(s) com pelo menos um campo nulo, em branco ou com telefone igual a 1234.",
            estilos["normal"],
        )
    )
    historia.append(Spacer(1, 5 * mm))

    resumo_linhas = [[Paragraph("Nome do membro", estilos["rotulo"]), Paragraph("Campos pendentes", estilos["rotulo"])]]
    for membro in membros:
        nome = formatar_valor("nome_do_membro", membro.dados.get("nome_do_membro")) or "(sem nome)"
        pendencias = ", ".join(ROTULOS.get(c, c) for c in membro.faltantes)
        resumo_linhas.append([
            Paragraph(nome, estilos["valor"]),
            Paragraph(pendencias, estilos["valor"]),
        ])

    tabela_resumo = Table(resumo_linhas, colWidths=[60 * mm, 120 * mm], repeatRows=1)
    tabela_resumo.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#163A63")),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
                ("BACKGROUND", (0, 1), (-1, -1), colors.HexColor("#F8FAFC")),
                ("BOX", (0, 0), (-1, -1), 0.5, colors.HexColor("#94A3B8")),
                ("INNERGRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#CBD5E1")),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
                ("TOPPADDING", (0, 0), (-1, -1), 5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
            ]
        )
    )
    historia.append(tabela_resumo)

    for idx, membro in enumerate(membros, start=1):
        historia.append(PageBreak())

        nome = formatar_valor("nome_do_membro", membro.dados.get("nome_do_membro")) or "(sem nome)"
        historia.append(Paragraph("FORMULARIO DE CONFERENCIA CADASTRAL", estilos["titulo"]))
        historia.append(Paragraph(f"Membro: <b>{nome}</b>", estilos["normal"]))
        historia.append(Paragraph(f"ID do membro: <b>{membro.dados.get('id_membro', '')}</b>", estilos["normal"]))
        historia.append(Paragraph(
            "Campos preenchidos aparecem com seus valores atuais. Campos nulos, vazios ou telefone igual a 1234 aparecem com linha para preenchimento manual.",
            estilos["pequeno"],
        ))
        historia.append(Spacer(1, 4 * mm))

        for titulo_secao, campos in SECOES.items():
            historia.append(Paragraph(titulo_secao, estilos["secao"]))
            historia.append(tabela_campos(membro, campos, estilos))
            historia.append(Spacer(1, 4 * mm))

        historia.append(Paragraph("3. Declaracao", estilos["secao"]))
        declaracao = (
            "Declaro que as informacoes acima foram fornecidas por mim e autorizo seu uso para fins de cadastro, "
            "comunicacao e organizacao interna da igreja."
        )
        autorizacao_foto = (
            "[  ] Autorizo a IPI de Muzambinho a cadastrar minha foto no sistema da igreja, "
            "para fins de identificacao interna, organizacao cadastral e controle de presencas."
        )
        declaracao_tabela = Table(
            [
                [Paragraph(declaracao, estilos["valor"])],
                [Paragraph(autorizacao_foto, estilos["valor"])],
                [Paragraph("Local e data: " + linha_vazia(28), estilos["valor"])],
                [Paragraph("Assinatura: " + linha_vazia(36), estilos["valor"])],
            ],
            colWidths=[180 * mm],
        )
        declaracao_tabela.setStyle(
            TableStyle(
                [
                    ("BACKGROUND", (0, 0), (-1, -1), colors.HexColor("#F8FAFC")),
                    ("BOX", (0, 0), (-1, -1), 0.5, colors.HexColor("#CBD5E1")),
                    ("INNERGRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#E2E8F0")),
                    ("LEFTPADDING", (0, 0), (-1, -1), 7),
                    ("RIGHTPADDING", (0, 0), (-1, -1), 7),
                    ("TOPPADDING", (0, 0), (-1, -1), 7),
                    ("BOTTOMPADDING", (0, 0), (-1, -1), 7),
                ]
            )
        )
        historia.append(declaracao_tabela)
        historia.append(Spacer(1, 3 * mm))
        historia.append(Paragraph(
            f"Pendencias identificadas para este membro: <b>{', '.join(ROTULOS.get(c, c) for c in membro.faltantes)}</b>",
            estilos["pequeno"],
        ))
        historia.append(Paragraph(f"Registro {idx} de {len(membros)}.", estilos["pequeno"]))

    doc.build(historia)


# ============================================================
# EXECUCAO
# ============================================================
def main() -> None:
    try:
        membros = buscar_membros_com_faltas()
        if not membros:
            print("Nenhum membro com pendencias cadastrais foi encontrado.")
            return

        gerar_pdf(membros, OUTPUT_PDF)
        print(f"PDF gerado com sucesso: {OUTPUT_PDF}")
    except Error as erro_mysql:
        print(f"Erro ao acessar o MySQL: {erro_mysql}")
    except Exception as erro:
        print(f"Erro inesperado: {erro}")
        raise


if __name__ == "__main__":
    main()
