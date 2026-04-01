# -*- coding: utf-8 -*-
"""
boas_vindas.py
Gera um PDF de boas-vindas para um visitante cadastrado no banco MySQL.

Uso:
python boas_vindas.py --id 5
python boas_vindas.py --id 5 --saida boas_vindas_visitante_5.pdf
"""

import argparse
import os
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle
import mysql.connector

# Ajuste conforme seu ambiente
DB_CONFIG = {
    "host": "localhost",
    "user": "SEU_USUARIO",
    "password": "SUA_SENHA",
    "database": "SEU_BANCO",
}

NOME_IGREJA = "Sua Igreja"
CIDADE_IGREJA = "Sua Cidade"
ESTADO_IGREJA = "UF"


def buscar_visitante(id_visitante: int):
    conn = mysql.connector.connect(**DB_CONFIG)
    cur = conn.cursor(dictionary=True)
    cur.execute("SELECT * FROM visitantes WHERE id_visitante = %s", (id_visitante,))
    visitante = cur.fetchone()
    cur.close()
    conn.close()
    return visitante


def gerar_pdf(visitante: dict, saida: str):
    styles = getSampleStyleSheet()
    styles.add(ParagraphStyle(
        name="TitleCenter",
        parent=styles["Title"],
        alignment=TA_CENTER,
        fontSize=20,
        leading=24,
        textColor=colors.HexColor("#1f3b5b")
    ))
    styles.add(ParagraphStyle(
        name="BodyJust",
        parent=styles["BodyText"],
        alignment=TA_JUSTIFY,
        fontSize=11.5,
        leading=17
    ))
    styles.add(ParagraphStyle(
        name="SmallCenter",
        parent=styles["BodyText"],
        alignment=TA_CENTER,
        fontSize=9.5,
        textColor=colors.HexColor("#666666")
    ))

    doc = SimpleDocTemplate(
        saida,
        pagesize=A4,
        rightMargin=2 * cm,
        leftMargin=2 * cm,
        topMargin=2 * cm,
        bottomMargin=2 * cm
    )

    story = []
    story.append(Paragraph("Carta de Boas-Vindas", styles["TitleCenter"]))
    story.append(Spacer(1, 0.5 * cm))
    story.append(Paragraph("Visitante cadastrado no sistema", styles["SmallCenter"]))
    story.append(Spacer(1, 0.8 * cm))

    nome = visitante.get("nome", "Visitante")

    textos = [
        f"Querido(a) <b>{nome}</b>,",
        "É uma alegria receber sua visita. Em nome da igreja, queremos lhe dar as boas-vindas e agradecer por ter estado conosco.",
        "Nossa oração é que você se sinta acolhido(a), cuidado(a) e edificado(a) entre nós. Desejamos que sua caminhada com Deus seja fortalecida a cada dia, e que este contato seja o começo de uma aproximação sincera, fraterna e abençoada.",
        "Estamos à disposição para orar com você, ouvir suas necessidades e caminhar ao seu lado no que for possível. Caso deseje, teremos alegria em recebê-lo(a) novamente em nossos cultos, aulas, reuniões e demais atividades.",
        "Seja muito bem-vindo(a).",
        f"Com carinho e em Cristo,<br/><b>{NOME_IGREJA}</b><br/>{CIDADE_IGREJA} - {ESTADO_IGREJA}",
    ]

    for t in textos:
        story.append(Paragraph(t, styles["BodyJust"]))
        story.append(Spacer(1, 0.35 * cm))

    story.append(Spacer(1, 1 * cm))
    assinatura = Table(
        [["__________________________________", ""], ["Responsável pelo contato", "Data: ____/____/________"]],
        colWidths=[8 * cm, 6 * cm]
    )
    assinatura.setStyle(TableStyle([
        ("FONTNAME", (0, 0), (-1, -1), "Helvetica"),
        ("FONTSIZE", (0, 0), (-1, -1), 10),
        ("ALIGN", (0, 0), (-1, -1), "CENTER"),
        ("TOPPADDING", (0, 0), (-1, -1), 8),
    ]))
    story.append(assinatura)

    doc.build(story)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--id", type=int, required=True, help="ID do visitante")
    parser.add_argument("--saida", type=str, default="boas_vindas.pdf", help="Nome do PDF de saída")
    args = parser.parse_args()

    visitante = buscar_visitante(args.id)
    if not visitante:
        print("Visitante não encontrado.")
        return

    gerar_pdf(visitante, args.saida)
    print(f"PDF gerado com sucesso: {args.saida}")


if __name__ == "__main__":
    main()
