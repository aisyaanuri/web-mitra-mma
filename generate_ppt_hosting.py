import sys
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

def create_deck():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6] # completely blank layout

    # Color Palette (Corporate PT Mustika)
    C_NAVY_DARK = RGBColor(15, 37, 78)       # #0F254E
    C_NAVY_DEEP = RGBColor(10, 23, 51)       # #0A1733
    C_BLUE_BRAND = RGBColor(29, 78, 216)     # #1D4ED8
    C_BLUE_LIGHT = RGBColor(224, 239, 254)   # #E0EFFE
    C_WHITE = RGBColor(255, 255, 255)
    C_SLATE_BG = RGBColor(248, 250, 252)     # #F8FAFC
    C_SLATE_CARD = RGBColor(255, 255, 255)
    C_TEXT_DARK = RGBColor(15, 23, 42)       # #0F172A
    C_TEXT_MUTED = RGBColor(100, 116, 139)   # #64748B
    C_BORDER_LIGHT = RGBColor(226, 232, 240) # #E2E8F0
    C_EMERALD = RGBColor(5, 150, 105)        # #059669
    C_EMERALD_BG = RGBColor(236, 253, 245)   # #ECFDF5
    C_AMBER = RGBColor(217, 119, 6)          # #D97706
    C_AMBER_BG = RGBColor(254, 243, 199)     # #FEF3C7

    def add_bg(slide, color):
        bg = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(7.5))
        bg.fill.solid()
        bg.fill.fore_color.rgb = color
        bg.line.fill.background()
        return bg

    # =========================================================================
    # SLIDE 1: COVER SLIDE
    # =========================================================================
    slide1 = prs.slides.add_slide(blank_layout)
    add_bg(slide1, C_NAVY_DARK)

    # Decorative top bar
    top_bar = slide1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(0.12))
    top_bar.fill.solid()
    top_bar.fill.fore_color.rgb = C_BLUE_BRAND
    top_bar.line.fill.background()

    # Category Badge
    badge1 = slide1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.2), Inches(1.5), Inches(3.8), Inches(0.45))
    badge1.fill.solid()
    badge1.fill.fore_color.rgb = RGBColor(30, 58, 115)
    badge1.line.color.rgb = C_BLUE_BRAND
    badge1.line.width = Pt(1)
    tf_b1 = badge1.text_frame
    tf_b1.vertical_anchor = MSO_ANCHOR.MIDDLE
    p_b1 = tf_b1.paragraphs[0]
    p_b1.text = "ANALISIS INFRASTRUKTUR TEKNOLOGI"
    p_b1.font.name = "Arial"
    p_b1.font.size = Pt(11)
    p_b1.font.bold = True
    p_b1.font.color.rgb = RGBColor(147, 197, 253)

    # Main Title
    tx_title = slide1.shapes.add_textbox(Inches(1.2), Inches(2.2), Inches(11.0), Inches(2.2))
    tf_t = tx_title.text_frame
    tf_t.word_wrap = True
    p_t1 = tf_t.paragraphs[0]
    p_t1.text = "Rekomendasi Penyedia Hosting & Email Resmi"
    p_t1.font.name = "Arial"
    p_t1.font.size = Pt(38)
    p_t1.font.bold = True
    p_t1.font.color.rgb = C_WHITE

    p_t2 = tf_t.add_paragraph()
    p_t2.text = "Sistem Web & Manajemen Distribusi PT Mustika Makmur Abadi"
    p_t2.font.name = "Arial"
    p_t2.font.size = Pt(20)
    p_t2.font.bold = True
    p_t2.font.color.rgb = RGBColor(191, 219, 254)
    p_t2.space_before = Pt(12)

    # Tech Stack Highlights Strip
    tags = [
        "Framework: Laravel 11/12",
        "Runtime: PHP 8.3+",
        "Database: MySQL & Livewire",
        "Fitur: Shopee Sync & Rekonsiliasi",
        "Target: Email Domain @mustikamakmurabadi.com"
    ]
    x_pos = Inches(1.2)
    for tag in tags:
        w_tag = Inches(2.1)
        t_box = slide1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, x_pos, Inches(4.7), w_tag, Inches(0.55))
        t_box.fill.solid()
        t_box.fill.fore_color.rgb = RGBColor(22, 45, 90)
        t_box.line.color.rgb = RGBColor(40, 75, 145)
        tf_tag = t_box.text_frame
        tf_tag.vertical_anchor = MSO_ANCHOR.MIDDLE
        p_tag = tf_tag.paragraphs[0]
        p_tag.alignment = PP_ALIGN.CENTER
        p_tag.text = tag
        p_tag.font.name = "Arial"
        p_tag.font.size = Pt(9.5)
        p_tag.font.bold = True
        p_tag.font.color.rgb = C_WHITE
        x_pos += Inches(2.2)

    # Footer note
    tx_f1 = slide1.shapes.add_textbox(Inches(1.2), Inches(6.2), Inches(11.0), Inches(0.6))
    p_f1 = tx_f1.text_frame.paragraphs[0]
    p_f1.text = "Laporan Analisis Komparasi Teknis & Biaya Riil | Evaluasi: Hostinger vs DomaiNesia vs Rumahweb"
    p_f1.font.name = "Arial"
    p_f1.font.size = Pt(11)
    p_f1.font.color.rgb = RGBColor(148, 163, 184)

    # =========================================================================
    # SLIDE 2: MATRIKS PERBANDINGAN
    # =========================================================================
    slide2 = prs.slides.add_slide(blank_layout)
    add_bg(slide2, C_SLATE_BG)

    # Header section
    tx_h2 = slide2.shapes.add_textbox(Inches(0.8), Inches(0.5), Inches(11.7), Inches(1.1))
    tf_h2 = tx_h2.text_frame
    p_h2 = tf_h2.paragraphs[0]
    p_h2.text = "Matriks Perbandingan 3 Provider Hosting Teratas"
    p_h2.font.name = "Arial"
    p_h2.font.size = Pt(26)
    p_h2.font.bold = True
    p_h2.font.color.rgb = C_TEXT_DARK

    p_sub2 = tf_h2.add_paragraph()
    p_sub2.text = "Evaluasi spesifikasi teknis untuk kebutuhan Laravel 11/12 (PHP 8.3) dan operasional email bisnis PT Mustika."
    p_sub2.font.name = "Arial"
    p_sub2.font.size = Pt(13)
    p_sub2.font.color.rgb = C_TEXT_MUTED
    p_sub2.space_before = Pt(4)

    # Table Shape
    rows = 7
    cols = 4
    left = Inches(0.8)
    top = Inches(1.7)
    width = Inches(11.733)
    height = Inches(5.1)

    table_shape = slide2.shapes.add_table(rows, cols, left, top, width, height)
    tbl = table_shape.table

    tbl.columns[0].width = Inches(2.533) # Kriteria
    tbl.columns[1].width = Inches(3.1)   # Hostinger
    tbl.columns[2].width = Inches(3.05)  # DomaiNesia
    tbl.columns[3].width = Inches(3.05)  # Rumahweb

    table_data = [
        [
            ("Kriteria Evaluasi", True, C_NAVY_DARK, C_WHITE, Pt(11)),
            ("Hostinger (Paket Unlimited)", True, C_BLUE_BRAND, C_WHITE, Pt(11.5)),
            ("DomaiNesia (Nimbus Plus)", True, C_NAVY_DARK, C_WHITE, Pt(11)),
            ("Rumahweb (Unlimited L)", True, C_NAVY_DARK, C_WHITE, Pt(11))
        ],
        [
            ("Total Biaya (1 Tahun)", True, C_WHITE, C_TEXT_DARK, Pt(10.5)),
            ("Rp 797.868 / tahun\n(All-in: Domain + SSL + Backup)", False, C_WHITE, C_TEXT_DARK, Pt(10)),
            ("Rp 879.000 / tahun\n(Tanpa addon Speed Booster)", False, C_WHITE, C_TEXT_DARK, Pt(10)),
            ("~Rp 490.000 / tahun\n(Setelah hapus addon tak perlu)", False, C_WHITE, C_TEXT_DARK, Pt(10))
        ],
        [
            ("Storage & Disk Speed", True, C_SLATE_BG, C_TEXT_DARK, Pt(10.5)),
            ("50 GB NVMe Storage\n(Paling lega & super cepat)", True, C_SLATE_BG, C_BLUE_BRAND, Pt(10)),
            ("25 GB NVMe Storage\n(Cukup untuk katalog web)", False, C_SLATE_BG, C_TEXT_DARK, Pt(10)),
            ("Unlimited SSD\n(Kecepatan SSD standar)", False, C_SLATE_BG, C_TEXT_DARK, Pt(10))
        ],
        [
            ("Solusi Email Perusahaan", True, C_WHITE, C_TEXT_DARK, Pt(10.5)),
            ("Hostinger / Titan Mail\n(UI Modern mirip Gmail, sangat rapi)", True, C_WHITE, C_EMERALD, Pt(10)),
            ("cPanel Webmail (Roundcube)\n(Tampilan klasik / konvensional)", False, C_WHITE, C_TEXT_DARK, Pt(10)),
            ("cPanel Webmail (Roundcube)\n(Tampilan klasik / konvensional)", False, C_WHITE, C_TEXT_DARK, Pt(10))
        ],
        [
            ("Kesiapan Laravel & SSH", True, C_SLATE_BG, C_TEXT_DARK, Pt(10.5)),
            ("Lengkap: PHP 8.3, SSH, Git Deploy,\nCron Job (Shopee Sync lancar)", True, C_SLATE_BG, C_TEXT_DARK, Pt(10)),
            ("Lengkap: PHP 8.3, SSH, Cron Job,\nDokumentasi Laravel resmi", False, C_SLATE_BG, C_TEXT_DARK, Pt(10)),
            ("Tersedia: PHP 8.3, SSH (di Paket L),\nCron Job standar cPanel", False, C_SLATE_BG, C_TEXT_DARK, Pt(10))
        ],
        [
            ("Sistem Backup Rutin", True, C_WHITE, C_TEXT_DARK, Pt(10.5)),
            ("Gratis Backup Harian (Daily)\n(Data transaksi & stok aman)", True, C_WHITE, C_BLUE_BRAND, Pt(10)),
            ("Backup Harian Berbayar\n(Addon tambahan Rp118rb/thn)", False, C_WHITE, C_TEXT_DARK, Pt(10)),
            ("Gratis Backup Mingguan (Weekly)\n(Frekuensi per 7 hari)", False, C_WHITE, C_TEXT_DARK, Pt(10))
        ],
        [
            ("Lokasi Server & Latensi", True, C_SLATE_BG, C_TEXT_DARK, Pt(10.5)),
            ("Singapura + Cloudflare CDN\n(Akses stabil & aman serangan)", False, C_SLATE_BG, C_TEXT_DARK, Pt(10)),
            ("Jakarta, Indonesia (Lokal)\n(Akses kilat dari Jawa Timur)", True, C_SLATE_BG, C_EMERALD, Pt(10)),
            ("Jakarta, Indonesia (Lokal)\n(Akses kilat dari Jawa Timur)", True, C_SLATE_BG, C_EMERALD, Pt(10))
        ]
    ]

    for r_idx, row in enumerate(table_data):
        for c_idx, (text, is_bold, bg_col, text_col, f_size) in enumerate(row):
            cell = tbl.cell(r_idx, c_idx)
            cell.text = text
            cell.fill.solid()
            cell.fill.fore_color.rgb = bg_col
            cell.vertical_anchor = MSO_ANCHOR.MIDDLE
            for p in cell.text_frame.paragraphs:
                p.alignment = PP_ALIGN.LEFT if c_idx == 0 else PP_ALIGN.CENTER
                for run in p.runs:
                    run.font.name = "Arial"
                    run.font.size = f_size
                    run.font.bold = is_bold
                    run.font.color.rgb = text_col

    # =========================================================================
    # SLIDE 3: REKOMENDASI TERBAIK (WINNER)
    # =========================================================================
    slide3 = prs.slides.add_slide(blank_layout)
    add_bg(slide3, C_SLATE_BG)

    # Header
    tx_h3 = slide3.shapes.add_textbox(Inches(0.8), Inches(0.5), Inches(11.7), Inches(1.1))
    tf_h3 = tx_h3.text_frame
    p_h3 = tf_h3.paragraphs[0]
    p_h3.text = "Rekomendasi Utama: Hostinger Paket Unlimited"
    p_h3.font.name = "Arial"
    p_h3.font.size = Pt(26)
    p_h3.font.bold = True
    p_h3.font.color.rgb = C_TEXT_DARK

    p_sub3 = tf_h3.add_paragraph()
    p_sub3.text = "Pilihan paling seimbang antara performa website Laravel, kemudahan email staf, dan total biaya tahunan."
    p_sub3.font.name = "Arial"
    p_sub3.font.size = Pt(13)
    p_sub3.font.color.rgb = C_TEXT_MUTED
    p_sub3.space_before = Pt(4)

    # 3 Pillar Cards
    pillars = [
        (
            "1. Pengalaman Email Bisnis Terbaik",
            "Sangat Penting untuk Tim Kantor",
            C_EMERALD,
            C_EMERALD_BG,
            [
                "Webmail Modern (Titan): Tampilan email sangat bersih, rapi, dan mudah dipakai mirip Gmail web.",
                "Kredibilitas Korporat: Bebas membuat alamat email resmi (misal: order@mustikamakmurabadi.com).",
                "Reputasi Domain Aman: Pengaturan DNS SPF, DKIM, dan DMARC otomatis meminimalisir risiko masuk SPAM."
            ]
        ),
        (
            "2. Spesifikasi Storage & Proteksi Tertinggi",
            "50 GB NVMe + Backup Harian Gratis",
            C_BLUE_BRAND,
            C_BLUE_LIGHT,
            [
                "Storage 50 GB NVMe: Kapasitas 2x lipat lebih besar dibanding opsi lain, siap menampung katalog foto HD.",
                "Gratis Backup Harian: Pemulihan data otomatis 1-klik jika terjadi kendala teknis atau database.",
                "Paket All-in-One: Sudah mencakup domain .com gratis, sertifikat SSL unlimited, dan proteksi privasi WHOIS."
            ]
        ),
        (
            "3. Kesiapan Penuh Ekosistem Laravel",
            "PHP 8.3 & Integrasi Job Shopee",
            C_AMBER,
            C_AMBER_BG,
            [
                "Akses Terminal SSH: Memudahkan proses migration, cache clear, dan composer install.",
                "Background Cron Job: Scheduler Laravel berjalan lancar untuk sinkronisasi pesanan Shopee & rekonsiliasi.",
                "Git Repository Deploy: Pembaruan kode web dapat di-deploy secara otomatis dari repository."
            ]
        )
    ]

    card_w = Inches(3.64)
    gap = Inches(0.4)
    x_c = Inches(0.8)

    for title, subtitle, accent_col, bg_accent, bullet_points in pillars:
        card = slide3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, x_c, Inches(1.8), card_w, Inches(5.0))
        card.fill.solid()
        card.fill.fore_color.rgb = C_WHITE
        card.line.color.rgb = C_BORDER_LIGHT
        card.line.width = Pt(1.5)

        # Header accent bar inside card
        header_bar = slide3.shapes.add_shape(MSO_SHAPE.RECTANGLE, x_c, Inches(1.8), card_w, Inches(0.1))
        header_bar.fill.solid()
        header_bar.fill.fore_color.rgb = accent_col
        header_bar.line.fill.background()

        # Text Frame
        tx_card = slide3.shapes.add_textbox(x_c + Inches(0.2), Inches(2.1), card_w - Inches(0.4), Inches(4.5))
        tf_c = tx_card.text_frame
        tf_c.word_wrap = True

        p_ct = tf_c.paragraphs[0]
        p_ct.text = title
        p_ct.font.name = "Arial"
        p_ct.font.size = Pt(14)
        p_ct.font.bold = True
        p_ct.font.color.rgb = C_TEXT_DARK

        p_cs = tf_c.add_paragraph()
        p_cs.text = subtitle
        p_cs.font.name = "Arial"
        p_cs.font.size = Pt(10.5)
        p_cs.font.bold = True
        p_cs.font.color.rgb = accent_col
        p_cs.space_before = Pt(4)
        p_cs.space_after = Pt(14)

        for bp in bullet_points:
            p_b = tf_c.add_paragraph()
            p_b.text = "• " + bp
            p_b.font.name = "Arial"
            p_b.font.size = Pt(10)
            p_b.font.color.rgb = RGBColor(51, 65, 85)
            p_b.space_after = Pt(10)

        x_c += card_w + gap

    # =========================================================================
    # SLIDE 4: PANDUAN PEMESANAN & ACTION ITEMS
    # =========================================================================
    slide4 = prs.slides.add_slide(blank_layout)
    add_bg(slide4, C_SLATE_BG)

    # Header
    tx_h4 = slide4.shapes.add_textbox(Inches(0.8), Inches(0.5), Inches(11.7), Inches(1.1))
    tf_h4 = tx_h4.text_frame
    p_h4 = tf_h4.paragraphs[0]
    p_h4.text = "Panduan Pemesanan & Alokasi Anggaran Efisien"
    p_h4.font.name = "Arial"
    p_h4.font.size = Pt(26)
    p_h4.font.bold = True
    p_h4.font.color.rgb = C_TEXT_DARK

    p_sub4 = tf_h4.add_paragraph()
    p_sub4.text = "Rekomendasi eksekusi final untuk menghindari biaya add-on yang tidak diperlukan dan memastikan deployment sukses."
    p_sub4.font.name = "Arial"
    p_sub4.font.size = Pt(13)
    p_sub4.font.color.rgb = C_TEXT_MUTED
    p_sub4.space_before = Pt(4)

    # 2 Big Section Cards
    # Left Card: Hindari Add-on Tak Perlu (Warning)
    w_card = Inches(5.66)
    h_card = Inches(5.0)

    card_left = slide4.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.8), w_card, h_card)
    card_left.fill.solid()
    card_left.fill.fore_color.rgb = C_WHITE
    card_left.line.color.rgb = RGBColor(253, 230, 138)
    card_left.line.width = Pt(1.5)

    tx_cl = slide4.shapes.add_textbox(Inches(1.1), Inches(2.1), w_card - Inches(0.6), h_card - Inches(0.6))
    tf_cl = tx_cl.text_frame
    tf_cl.word_wrap = True

    p_cl1 = tf_cl.paragraphs[0]
    p_cl1.text = "⚠️ Perhatian: Hindari Add-on yang Membengkak"
    p_cl1.font.name = "Arial"
    p_cl1.font.size = Pt(16)
    p_cl1.font.bold = True
    p_cl1.font.color.rgb = C_AMBER

    items_warn = [
        ("Jangan Beli SSL Berbayar (Sectigo Rp99rb)", "Semua provider sudah menyediakan SSL Let's Encrypt gratis selamanya dengan keamanan setara."),
        ("Jangan Beli RankingCoach / SEO AI (Rp144rb)", "Web PT Mustika sudah diprogram dengan meta tags, sitemap.xml otomatis, dan OpenGraph custom."),
        ("Jangan Tambah Speed Booster Berbayar", "Untuk trafik awal katalog distributor, server NVMe bawaan sudah sangat responsif tanpa biaya tambahan."),
        ("Kesimpulan Anggaran", "Pilihan Hostinger seharga Rp797.868/tahun sudah 'bersih' tanpa biaya tersembunyi.")
    ]

    for title, desc in items_warn:
        p_w1 = tf_cl.add_paragraph()
        p_w1.text = "• " + title
        p_w1.font.name = "Arial"
        p_w1.font.size = Pt(11)
        p_w1.font.bold = True
        p_w1.font.color.rgb = C_TEXT_DARK
        p_w1.space_before = Pt(8)

        p_w2 = tf_cl.add_paragraph()
        p_w2.text = desc
        p_w2.font.name = "Arial"
        p_w2.font.size = Pt(9.5)
        p_w2.font.color.rgb = C_TEXT_MUTED
        p_w2.space_before = Pt(1)

    # Right Card: Action Plan / Langkah Selanjutnya
    card_right = slide4.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.866), Inches(1.8), w_card, h_card)
    card_right.fill.solid()
    card_right.fill.fore_color.rgb = C_NAVY_DARK
    card_right.line.color.rgb = C_BLUE_BRAND
    card_right.line.width = Pt(1.5)

    tx_cr = slide4.shapes.add_textbox(Inches(7.166), Inches(2.1), w_card - Inches(0.6), h_card - Inches(0.6))
    tf_cr = tx_cr.text_frame
    tf_cr.word_wrap = True

    p_cr1 = tf_cr.paragraphs[0]
    p_cr1.text = "🚀 4 Langkah Tindakan (Action Plan)"
    p_cr1.font.name = "Arial"
    p_cr1.font.size = Pt(16)
    p_cr1.font.bold = True
    p_cr1.font.color.rgb = RGBColor(147, 197, 253)

    steps = [
        ("Langkah 1: Ambil Paket Hostinger Unlimited (1 Tahun)", "Alokasikan anggaran Rp 797.868 untuk sewa 12 bulan (sudah termasuk domain gratis)."),
        ("Langkah 2: Registrasi Nama Domain Resmi", "Pilih domain resmi perusahaan, misal: ptmustikamakmurabadi.com atau mustikamakmurabadi.com."),
        ("Langkah 3: Pembuatan Akun Email Bisnis", "Buat email resmi: direksi@, sales@, order@, dan admin@ melalui panel Hostinger Mail."),
        ("Langkah 4: Deploy Proyek Laravel & Setup Database", "Hubungkan Git repository, import database MySQL, jalankan cron job sync Shopee, dan aktifkan SSL.")
    ]

    for title, desc in steps:
        p_s1 = tf_cr.add_paragraph()
        p_s1.text = title
        p_s1.font.name = "Arial"
        p_s1.font.size = Pt(11)
        p_s1.font.bold = True
        p_s1.font.color.rgb = C_WHITE
        p_s1.space_before = Pt(8)

        p_s2 = tf_cr.add_paragraph()
        p_s2.text = desc
        p_s2.font.name = "Arial"
        p_s2.font.size = Pt(9.5)
        p_s2.font.color.rgb = RGBColor(203, 213, 225)
        p_s2.space_before = Pt(1)

    # Save presentation
    output_filename = "Rekomendasi_Hosting_PT_Mustika.pptx"
    prs.save(output_filename)
    print(f"Presentation saved successfully to: {output_filename}")

if __name__ == "__main__":
    create_deck()
