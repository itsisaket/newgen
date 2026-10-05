import argparse
from pathlib import Path

import pypdfium2 as pdfium
from docx import Document
from docx.oxml.ns import qn
from docx.table import Table as DocxTable
from docx.text.paragraph import Paragraph as DocxParagraph
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import inch
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import PageBreak, Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle


ROOT = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument("docx", nargs="?", default=str(ROOT / "docs" / "แบบสำรวจเกษตรกรชาวสวนทุเรียน_DRFI.docx"))
parser.add_argument("--output_dir", default=str(ROOT / "storage" / "app" / "docx_qa" / "survey_fallback"))
args = parser.parse_args()
DOCX = Path(args.docx).resolve()
OUT_DIR = Path(args.output_dir).resolve()
PDF = OUT_DIR / f"{DOCX.stem}_preview.pdf"

pdfmetrics.registerFont(TTFont("Tahoma", r"C:\Windows\Fonts\tahoma.ttf"))
pdfmetrics.registerFont(TTFont("Tahoma-Bold", r"C:\Windows\Fonts\tahomabd.ttf"))


def iter_blocks(parent):
    parent_elm = parent.element.body
    for child in parent_elm.iterchildren():
        if child.tag == qn("w:p"):
            yield DocxParagraph(child, parent)
        elif child.tag == qn("w:tbl"):
            yield DocxTable(child, parent)


body = ParagraphStyle("Body", fontName="Tahoma", fontSize=9.5, leading=13, textColor=colors.black, spaceAfter=4)
title = ParagraphStyle("Title", parent=body, fontName="Tahoma-Bold", fontSize=19, leading=25, alignment=TA_CENTER, spaceAfter=9)
page_title = ParagraphStyle("PageTitle", parent=body, fontName="Tahoma-Bold", fontSize=16, leading=21, alignment=TA_CENTER, spaceAfter=9)
section = ParagraphStyle("Section", parent=body, fontName="Tahoma-Bold", fontSize=12, leading=16, backColor=colors.HexColor("#EAF4EC"), borderPadding=5, spaceBefore=3, spaceAfter=6)
small = ParagraphStyle("Small", parent=body, fontSize=8.4, leading=11)
cell_style = ParagraphStyle("Cell", parent=body, fontSize=7.5, leading=9, spaceAfter=0)
cell_header = ParagraphStyle("CellHeader", parent=cell_style, fontName="Tahoma-Bold", textColor=colors.white, alignment=TA_CENTER)


def safe(text):
    return (text or "").replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;").replace("\n", "<br/>")


story = []
docx = Document(DOCX)
first_title_seen = False
for block in iter_blocks(docx):
    if isinstance(block, DocxParagraph):
        xml = block._p.xml
        has_page_break = 'w:type="page"' in xml
        text = block.text.strip()
        if has_page_break:
            story.append(PageBreak())
            continue
        if not text:
            story.append(Spacer(1, 2))
            continue
        if block.style.name == "Title":
            style = title
            first_title_seen = True
        elif 'w:fill="EAF4EC"' in xml:
            style = section
        elif block.alignment == 1 and len(text) < 80:
            style = page_title
        elif len(text) > 180:
            style = small
        else:
            style = body
        story.append(Paragraph(safe(text), style))
    else:
        data = []
        for ridx, row in enumerate(block.rows):
            data.append([
                Paragraph(safe(cell.text), cell_header if ridx == 0 else cell_style)
                for cell in row.cells
            ])
        if not data:
            continue
        raw_widths = []
        for cell in block.rows[0].cells:
            raw_widths.append(cell.width.inches if cell.width else 1)
        total = sum(raw_widths) or len(raw_widths)
        widths = [7.25 * inch * (w / total) for w in raw_widths]
        table = Table(data, colWidths=widths, repeatRows=1, hAlign="CENTER")
        table.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#1F6B35")),
            ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
            ("GRID", (0, 0), (-1, -1), 0.45, colors.HexColor("#D9D9D9")),
            ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
            ("LEFTPADDING", (0, 0), (-1, -1), 4),
            ("RIGHTPADDING", (0, 0), (-1, -1), 4),
            ("TOPPADDING", (0, 0), (-1, -1), 5),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
        ]))
        for ridx in range(2, len(data), 2):
            table.setStyle(TableStyle([("BACKGROUND", (0, ridx), (-1, ridx), colors.HexColor("#F5F6F7"))]))
        story.extend([table, Spacer(1, 4)])


OUT_DIR.mkdir(parents=True, exist_ok=True)
pdf = SimpleDocTemplate(
    str(PDF), pagesize=letter,
    leftMargin=0.62 * inch, rightMargin=0.62 * inch,
    topMargin=0.5 * inch, bottomMargin=0.55 * inch,
    title=DOCX.stem,
)


def footer(canvas, document):
    canvas.saveState()
    canvas.setFont("Tahoma", 7.5)
    canvas.setFillColor(colors.HexColor("#666666"))
    canvas.drawCentredString(letter[0] / 2, 0.28 * inch, f"DRFIS  ชุดเครื่องมือเก็บข้อมูลภาคสนามรายปี  |  หน้า {document.page}")
    canvas.restoreState()


pdf.build(story, onFirstPage=footer, onLaterPages=footer)

rendered = pdfium.PdfDocument(str(PDF))
for index in range(len(rendered)):
    page = rendered[index]
    bitmap = page.render(scale=1.6)
    bitmap.to_pil().save(OUT_DIR / f"page-{index + 1}.png")
print(f"pages={len(rendered)} pdf={PDF}")
