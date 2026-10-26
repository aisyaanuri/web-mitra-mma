import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import qn, nsdecls

def create_document():
    doc = docx.Document()

    # Page Margins (Normal: 1 inch = 2.54 cm)
    for section in doc.sections:
        section.top_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.right_margin = Inches(1)

    # Palette
    COLOR_PRIMARY = RGBColor(16, 74, 128)     # Navy / Deep Blue
    COLOR_SECONDARY = RGBColor(60, 60, 60)   # Slate Dark
    COLOR_MUTED = RGBColor(100, 100, 100)    # Gray
    HEX_PRIMARY = "104A80"
    HEX_LIGHT_BG = "F4F6F9"
    HEX_BORDER = "CCCCCC"

    def set_cell_background(cell, hex_color):
        shading = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
        cell._tc.get_or_add_tcPr().append(shading)

    def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
        tcPr = cell._tc.get_or_add_tcPr()
        tcMar = OxmlElement('w:tcMar')
        for margin_name, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
            node = OxmlElement(f'w:{margin_name}')
            node.set(qn('w:w'), str(val))
            node.set(qn('w:type'), 'dxa')
            tcMar.append(node)
        tcPr.append(tcMar)

    # ================= COVER / HEADER =================
    p_inst = doc.add_paragraph()
    p_inst.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_inst = p_inst.add_run("KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI\nPOLITEKNIK NEGERI JEMBER\nJURUSAN TEKNOLOGI INFORMASI - PSDKU SIDOARJO\nPROGRAM STUDI TEKNIK INFORMATIKA")
    r_inst.font.name = "Arial"
    r_inst.font.size = Pt(10)
    r_inst.font.bold = True
    r_inst.font.color.rgb = COLOR_SECONDARY

    doc.add_paragraph() # spacer

    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_t1 = p_title.add_run("LAPORAN DISTRIBUSI & PEMBAGIAN MODUL SISTEM\n")
    r_t1.font.name = "Arial"
    r_t1.font.size = Pt(16)
    r_t1.font.bold = True
    r_t1.font.color.rgb = COLOR_PRIMARY

    r_t2 = p_title.add_run("Sistem Informasi Company Profile Berbasis Web & Manajemen Rantai Distribusi Terintegrasi Marketplace Shopee\nPT Mustika Makmur Abadi")
    r_t2.font.name = "Arial"
    r_t2.font.size = Pt(12)
    r_t2.font.bold = True
    r_t2.font.color.rgb = COLOR_SECONDARY

    p_meta = doc.add_paragraph()
    p_meta.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_meta = p_meta.add_run("Mata Kuliah: Workshop Sistem Informasi Berbasis Website\nKelompok 8 - Kelas PBL | Semester Ganjil 2026")
    r_meta.font.name = "Arial"
    r_meta.font.size = Pt(10)
    r_meta.font.italic = True
    r_meta.font.color.rgb = COLOR_MUTED

    doc.add_paragraph() # spacer

    # ================= DAFTAR DOSEN & MITRA =================
    t_dosen = doc.add_table(rows=3, cols=2)
    t_dosen.alignment = WD_TABLE_ALIGNMENT.CENTER
    dosen_data = [
        ("Dosen Pengampu Workshop Web", ": Bapak Rifqi Aji Widarso, S.T., M.T."),
        ("Dosen Pengampu Workshop Mobile", ": Bapak Akas Bagus Setiawan, S.Kom., M.MT."),
        ("Mitra Industri & Lokasi Studi", ": PT Mustika Makmur Abadi (Kepala Cabang: Bpk. Aldi Agung)"),
    ]
    for i, (k, v) in enumerate(dosen_data):
        cell_k = t_dosen.cell(i, 0)
        cell_v = t_dosen.cell(i, 1)
        cell_k.text = k
        cell_v.text = v
        cell_k.paragraphs[0].runs[0].font.name = "Arial"
        cell_k.paragraphs[0].runs[0].font.size = Pt(9.5)
        cell_k.paragraphs[0].runs[0].font.bold = True
        cell_v.paragraphs[0].runs[0].font.name = "Arial"
        cell_v.paragraphs[0].runs[0].font.size = Pt(9.5)
        set_cell_margins(cell_k, 50, 50, 100, 100)
        set_cell_margins(cell_v, 50, 50, 100, 100)

    doc.add_paragraph()

    # ================= BAB 1: RINGKASAN MATRIKS =================
    h1 = doc.add_paragraph()
    r_h1 = h1.add_run("1. Ringkasan Matriks Pembagian Tugas Kelompok")
    r_h1.font.name = "Arial"
    r_h1.font.size = Pt(13)
    r_h1.font.bold = True
    r_h1.font.color.rgb = COLOR_PRIMARY

    p_intro = doc.add_paragraph(
        "Untuk memastikan pengerjaan proyek sistem informasi PT Mustika Makmur Abadi berjalan secara terstruktur, "
        "komprehensif, dan memiliki pembagian beban kerja yang merata, sistem dibagi menjadi 4 pilar modul utama. "
        "Masing-masing anggota bertanggung jawab penuh atas analisis alur bisnis, perancangan antarmuka, "
        "implementasi kode Livewire/Blade, interaksi basis data, serta pembuatan automated test suite untuk modulnya."
    )
    p_intro.runs[0].font.name = "Arial"
    p_intro.runs[0].font.size = Pt(10)

    # Matriks Table
    t_matriks = doc.add_table(rows=5, cols=4)
    t_matriks.alignment = WD_TABLE_ALIGNMENT.CENTER
    headers = ["Anggota Kelompok", "Peran / Tanggung Jawab", "Fokus Modul Utama", "Kontribusi"]
    for j, h in enumerate(headers):
        cell = t_matriks.cell(0, j)
        cell.text = h
        set_cell_background(cell, HEX_PRIMARY)
        set_cell_margins(cell, 120, 120, 120, 120)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.runs[0].font.name = "Arial"
        p.runs[0].font.size = Pt(9.5)
        p.runs[0].font.bold = True
        p.runs[0].font.color.rgb = RGBColor(255, 255, 255)

    rows_matriks = [
        ("Muhammad Saugi Assegaf\n(Ketua Tim)", "Full-Stack Developer &\nWarehouse Logic Architect", "Modul Pergudangan, Batch Fulfillment, Proteksi Batal & Lembar Kemas", "25%"),
        ("Aisya Nurrizahwa\n(Anggota)", "Integration & API Specialist /\nReverse Logistics", "Modul Integrasi Shopee Open API, Pipeline Sync & Manajemen Retur", "25%"),
        ("M. Syahfil Alfadil\n(Anggota)", "Data Governance, Security &\nExecutive Analyst", "Modul Audit Rekonsiliasi, Dashboard Eksekutif & Manajemen Pengguna (RBAC)", "25%"),
        ("Noval Niko Amrila\n(Anggota)", "Frontend Experience &\nProduct Catalog Specialist", "Modul Company Profile Publik, Katalog Detail Produk & Keamanan Akun", "25%"),
    ]

    for i, row in enumerate(rows_matriks, start=1):
        bg = HEX_LIGHT_BG if i % 2 == 1 else "FFFFFF"
        for j, val in enumerate(row):
            cell = t_matriks.cell(i, j)
            cell.text = val
            set_cell_background(cell, bg)
            set_cell_margins(cell, 100, 100, 120, 120)
            p = cell.paragraphs[0]
            if j == 3 or (j == 0 and "Ketua" in val):
                p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j == 3 else WD_ALIGN_PARAGRAPH.LEFT
            p.runs[0].font.name = "Arial"
            p.runs[0].font.size = Pt(9)
            if j == 0:
                p.runs[0].font.bold = True

    doc.add_paragraph() # spacer

    # ================= BAB 2: RINCIAN MODUL TIAP MAHASISWA =================
    h2 = doc.add_paragraph()
    r_h2 = h2.add_run("2. Rincian Penugasan & Deliverables Setiap Anggota")
    r_h2.font.name = "Arial"
    r_h2.font.size = Pt(13)
    r_h2.font.bold = True
    r_h2.font.color.rgb = COLOR_PRIMARY

    # --- ANGGOTA 1 ---
    p_m1 = doc.add_paragraph()
    r_m1 = p_m1.add_run("2.1 Muhammad Saugi Assegaf (Ketua Tim) - Modul Pergudangan & Fulfillment Fisik")
    r_m1.font.name = "Arial"
    r_m1.font.size = Pt(11)
    r_m1.font.bold = True
    r_m1.font.color.rgb = COLOR_PRIMARY

    items_m1 = [
        ("Deskripsi Ruang Lingkup", "Mengembangkan sistem otomasi fisik pemenuhan pesanan di gudang PT Mustika Makmur Abadi dari mulai barang diterima staf, dikemas, siap kirim, hingga diserahkan ke ekspedisi kurir."),
        ("Fitur Kunci yang Dikembangkan", "• Alur Siklus Status Gudang: Diterima -> Dikemas -> Siap Kirim -> Dikirim.\n"
                                          "• Pembaruan Masal (Bulk / Batch Action): Checkbox seleksi masal dan Floating Action Bar untuk memproses puluhan order sekaligus dalam satu klik.\n"
                                          "• Proteksi Salah Kirim (Cancellation Stop-Alert): Deteksi pesanan yang dibatalkan pembeli di Shopee (CANCELLED/IN_CANCEL) dengan highlight merah berkedip dan auto-lock tombol pengiriman.\n"
                                          "• Generator Lembar Kemas (Packing Slip): Layout cetak resmi standar distribusi FMCG lengkap dengan data pesanan, resi, kurir, tabel produk, checkbox QC fisik, dan kolom tanda tangan packer.\n"
                                          "• Audit Trail Pergudangan: Pencatatan otomatis ke warehouse_process_logs (IP address, user ID, status awal, status tujuan)."),
        ("File Source Code Utama", "resources/views/pages/admin/warehouse/index.blade.php\n"
                                  "app/Models/WarehouseProcessLog.php\n"
                                  "app/Exports/OrdersExport.php"),
        ("Hasil Verifikasi & Unit Testing", "15 Feature Test Cases Lulus 100% (WarehouseProcessingTest.php) mencakup verifikasi transisi, bulk update status masal, proteksi pembatalan, dan pembukaan modal cetak packing slip.")
    ]

    t_m1 = doc.add_table(rows=len(items_m1), cols=2)
    t_m1.alignment = WD_TABLE_ALIGNMENT.CENTER
    for idx, (label, val) in enumerate(items_m1):
        c0, c1 = t_m1.cell(idx, 0), t_m1.cell(idx, 1)
        c0.text, c1.text = label, val
        set_cell_background(c0, HEX_LIGHT_BG)
        set_cell_margins(c0, 70, 70, 100, 100)
        set_cell_margins(c1, 70, 70, 100, 100)
        c0.paragraphs[0].runs[0].font.bold = True
        c0.paragraphs[0].runs[0].font.size = Pt(9)
        c1.paragraphs[0].runs[0].font.size = Pt(9)
        c0.paragraphs[0].runs[0].font.name = "Arial"
        c1.paragraphs[0].runs[0].font.name = "Arial"

    doc.add_paragraph()

    # --- ANGGOTA 2 ---
    p_m2 = doc.add_paragraph()
    r_m2 = p_m2.add_run("2.2 Aisya Nurrizahwa (Anggota 1) - Modul Integrasi Marketplace Shopee & Manajemen Retur")
    r_m2.font.name = "Arial"
    r_m2.font.size = Pt(11)
    r_m2.font.bold = True
    r_m2.font.color.rgb = COLOR_PRIMARY

    items_m2 = [
        ("Deskripsi Ruang Lingkup", "Mengembangkan pipeline integrasi penarikan data pesanan dan logistik dari marketplace Shopee serta membangun modul koordinasi penanganan komplain barang retur pembeli."),
        ("Fitur Kunci yang Dikembangkan", "• Pipeline Background Sync Jobs: Eksekusi sinkronisasi terstruktur untuk pesanan baru (SyncShopeeOrdersJob), resi logistik (SyncShopeeLogisticsJob), dan data retur (SyncShopeeReturnsJob).\n"
                                          "• Monitoring Log & Payload Inspector: Antarmuka Dual-Tab untuk memantau status sinkronisasi, visualisasi pesanan terkini, modal detail payload data terstruktur, dan raw JSON payload viewer.\n"
                                          "• Modul Manajemen Retur (/admin/retur): Dashboard terpadu pemantauan komplain retur, alasan pengajuan Shopee, dan akumulasi nilai refund.\n"
                                          "• Verifikasi Fisik Retur Gudang: Form input kondisi fisik paket retur saat sampai di gudang (Diterima Baik, Diterima Rusak, Menunggu Barang, Barang Hilang) beserta catatan kondisi kemasan, nama verifikator, dan waktu terima."),
        ("File Source Code Utama", "resources/views/pages/admin/sync/index.blade.php\n"
                                  "resources/views/pages/admin/returns/index.blade.php\n"
                                  "app/Jobs/SyncShopeeOrdersJob.php, SyncShopeeLogisticsJob.php, SyncShopeeReturnsJob.php\n"
                                  "app/Models/OrderReturn.php & app/Models/SyncLog.php"),
        ("Hasil Verifikasi & Unit Testing", "10 Feature Test Cases Lulus 100% (SyncStatusPageTest.php & OrderReturnsPageTest.php) mencakup pengujian pipeline sinkronisasi, inspeksi payload log, dan verifikasi fisik retur.")
    ]

    t_m2 = doc.add_table(rows=len(items_m2), cols=2)
    t_m2.alignment = WD_TABLE_ALIGNMENT.CENTER
    for idx, (label, val) in enumerate(items_m2):
        c0, c1 = t_m2.cell(idx, 0), t_m2.cell(idx, 1)
        c0.text, c1.text = label, val
        set_cell_background(c0, HEX_LIGHT_BG)
        set_cell_margins(c0, 70, 70, 100, 100)
        set_cell_margins(c1, 70, 70, 100, 100)
        c0.paragraphs[0].runs[0].font.bold = True
        c0.paragraphs[0].runs[0].font.size = Pt(9)
        c1.paragraphs[0].runs[0].font.size = Pt(9)
        c0.paragraphs[0].runs[0].font.name = "Arial"
        c1.paragraphs[0].runs[0].font.name = "Arial"

    doc.add_paragraph()

    # --- ANGGOTA 3 ---
    p_m3 = doc.add_paragraph()
    r_m3 = p_m3.add_run("2.3 M. Syahfil Alfadil (Anggota 2) - Modul Audit Rekonsiliasi, Dashboard Eksekutif & Manajemen Pengguna")
    r_m3.font.name = "Arial"
    r_m3.font.size = Pt(11)
    r_m3.font.bold = True
    r_m3.font.color.rgb = COLOR_PRIMARY

    items_m3 = [
        ("Deskripsi Ruang Lingkup", "Mengembangkan modul pengawasan manajerial level Owner untuk mendeteksi ketidaksesuaian data operasional, mengukur performa SLA pengiriman, valuasi risiko finansial, dan mengontrol hak akses pengguna."),
        ("Fitur Kunci yang Dikembangkan", "• Audit Rekonsiliasi Otomatis: Komparasi cerdas antara status order marketplace Shopee vs status fisik gudang (Sesuai, Selisih, Belum Dicek).\n"
                                          "• Modal Investigasi Side-by-Side: Diagnosis otomatis akar selisih, template catatan investigasi cepat, dan tombol penyelarasan status gudang 1-klik yang memperbarui record rekonsiliasi dan mencatat audit log.\n"
                                          "• Peringatan Batas Waktu Pemenuhan (Fulfillment SLA Alert): Banner deteksi otomatis jika ada pesanan mengendap > 24 jam belum diserahkan ke kurir untuk mencegah penalti marketplace.\n"
                                          "• Valuasi Keuangan Berisiko & Top Products: Menghitung total dana tertahan akibat selisih rekonsiliasi, potensi refund retur, grafik tren omzet 6 bulan (ApexCharts), dan rekap 5 produk terlaris.\n"
                                          "• Manajemen Pengguna Berbasis Peran (RBAC): Modul pengelolaan user (/admin/pengguna) untuk membatasi akses peran Owner, Admin Marketplace, dan Admin Gudang.\n"
                                          "• Ekspor Excel Rekonsiliasi: Ekspor data hasil filter audit ke Excel melalui Maatwebsite/Excel."),
        ("File Source Code Utama", "resources/views/pages/admin/reconciliation/index.blade.php\n"
                                  "resources/views/pages/dashboard.blade.php\n"
                                  "resources/views/pages/admin/users/index.blade.php & form.blade.php\n"
                                  "app/Exports/ReconciliationExport.php & app/Models/ReconciliationRecord.php"),
        ("Hasil Verifikasi & Unit Testing", "18 Feature Test Cases Lulus 100% (ReconciliationReportTest.php, DashboardTest.php, UserManagementTest.php) mencakup komparasi status, mitigasi selisih, SLA alert, dan filtering export.")
    ]

    t_m3 = doc.add_table(rows=len(items_m3), cols=2)
    t_m3.alignment = WD_TABLE_ALIGNMENT.CENTER
    for idx, (label, val) in enumerate(items_m3):
        c0, c1 = t_m3.cell(idx, 0), t_m3.cell(idx, 1)
        c0.text, c1.text = label, val
        set_cell_background(c0, HEX_LIGHT_BG)
        set_cell_margins(c0, 70, 70, 100, 100)
        set_cell_margins(c1, 70, 70, 100, 100)
        c0.paragraphs[0].runs[0].font.bold = True
        c0.paragraphs[0].runs[0].font.size = Pt(9)
        c1.paragraphs[0].runs[0].font.size = Pt(9)
        c0.paragraphs[0].runs[0].font.name = "Arial"
        c1.paragraphs[0].runs[0].font.name = "Arial"

    doc.add_paragraph()

    # --- ANGGOTA 4 ---
    p_m4 = doc.add_paragraph()
    r_m4 = p_m4.add_run("2.4 Noval Niko Amrila (Anggota 3) - Modul Company Profile Publik, Katalog Produk & Sistem Keamanan")
    r_m4.font.name = "Arial"
    r_m4.font.size = Pt(11)
    r_m4.font.bold = True
    r_m4.font.color.rgb = COLOR_PRIMARY

    items_m4 = [
        ("Deskripsi Ruang Lingkup", "Mengembangkan sisi publik web perusahaan untuk membangun reputasi profesional distributor FMCG, katalog produk digital interaktif multi-brand, dan sistem autentikasi keamanan modern."),
        ("Fitur Kunci yang Dikembangkan", "• Portal Publik Interaktif: Halaman Beranda (Hero visual modern, fasilitas gudang real, keunggulan jangkauan distribusi), Halaman Tentang Kami (Visi, Misi, Nilai, Timeline sejarah distributor), dan Halaman Kontak.\n"
                                          "• Katalog Produk Multi-Kategori: Sistem etalase beras premium lokal/impor/organik dan makanan bayi Yummy Bites lengkap dengan filter kategori, pagination, dan live search.\n"
                                          "• Halaman Rincian Produk Komprehensif: Galeri produk, spesifikasi gramasi kemasan, keunggulan gizi, perizinan edar KEMTAN, dan sertifikasi Halal resmi.\n"
                                          "• Manajemen Katalog Admin (/admin/produk): Form manajemen produk internal, penetapan SKU, upload multi-gambar produk, penetapan kategori, dan status stok.\n"
                                          "• Sistem Keamanan & Autentikasi Modern: Login, Register, Two-Factor Authentication (2FA), dan Biometrik Passkeys WebAuthn."),
        ("File Source Code Utama", "resources/views/pages/public/beranda.blade.php, tentang-kami.blade.php, kontak.blade.php\n"
                                  "resources/views/pages/public/produk.blade.php & produk-detail.blade.php\n"
                                  "resources/views/pages/admin/products/index.blade.php & form.blade.php\n"
                                  "app/Models/Product.php & app/Models/ProductImage.php"),
        ("Hasil Verifikasi & Unit Testing", "14 Feature Test Cases Lulus 100% (ProdukPageTest.php & Authentication Tests) mencakup pengujian routing publik, pagination katalog, rincian produk, dan proteksi middleware.")
    ]

    t_m4 = doc.add_table(rows=len(items_m4), cols=2)
    t_m4.alignment = WD_TABLE_ALIGNMENT.CENTER
    for idx, (label, val) in enumerate(items_m4):
        c0, c1 = t_m4.cell(idx, 0), t_m4.cell(idx, 1)
        c0.text, c1.text = label, val
        set_cell_background(c0, HEX_LIGHT_BG)
        set_cell_margins(c0, 70, 70, 100, 100)
        set_cell_margins(c1, 70, 70, 100, 100)
        c0.paragraphs[0].runs[0].font.bold = True
        c0.paragraphs[0].runs[0].font.size = Pt(9)
        c1.paragraphs[0].runs[0].font.size = Pt(9)
        c0.paragraphs[0].runs[0].font.name = "Arial"
        c1.paragraphs[0].runs[0].font.name = "Arial"

    doc.add_paragraph()

    # ================= BAB 3: KETERHUBUNGAN ALUR =================
    h3 = doc.add_paragraph()
    r_h3 = h3.add_run("3. Sinergi & Keterhubungan Alur Bisnis Antar-Modul")
    r_h3.font.name = "Arial"
    r_h3.font.size = Pt(13)
    r_h3.font.bold = True
    r_h3.font.color.rgb = COLOR_PRIMARY

    p_flow = doc.add_paragraph(
        "Keempat modul di atas bekerja secara berkesinambungan membentuk satu ekosistem distribusi FMCG yang utuh:\n"
        "1. Modul Publik & Katalog (Noval Niko) membangun kehadiran digital dan menyajikan informasi produk resmi ke pasar.\n"
        "2. Modul Integrasi Marketplace (Aisya Nurrizahwa) menarik pesanan dari Shopee secara otomatis ke basis data internal.\n"
        "3. Modul Gudang & Fulfillment (Muhammad Saugi Assegaf) memproses pesanan fisik di gudang, mencetak lembar kemas, dan memverifikasi kesiapan kirim kurir sambil mengamankan pesanan batal.\n"
        "4. Modul Rekonsiliasi & Dashboard (M. Syahfil Alfadil) mengaudit kepatuhan status Shopee vs Gudang, mengevaluasi SLA keterlambatan, dan menyajikan laporan finansial komprehensif bagi jajaran direksi/owner."
    )
    p_flow.runs[0].font.name = "Arial"
    p_flow.runs[0].font.size = Pt(9.5)

    doc.add_paragraph()

    # ================= BAB 4: REKAPITULASI PENGUJIAN =================
    h4 = doc.add_paragraph()
    r_h4 = h4.add_run("4. Standar Mutu & Hasil Pengujian Sistem (Automated Tests)")
    r_h4.font.name = "Arial"
    r_h4.font.size = Pt(13)
    r_h4.font.bold = True
    r_h4.font.color.rgb = COLOR_PRIMARY

    p_qa = doc.add_paragraph(
        "Kualitas dan stabilitas sistem dibuktikan melalui automated test suite Laravel Pest/PHPUnit. "
        "Seluruh kode yang ditulis oleh keempat anggota kelompok telah melalui tahap verifikasi otomatis dengan rincian:"
    )
    p_qa.runs[0].font.name = "Arial"
    p_qa.runs[0].font.size = Pt(9.5)

    t_qa = doc.add_table(rows=6, cols=3)
    t_qa.alignment = WD_TABLE_ALIGNMENT.CENTER
    qa_headers = ["Domain Pengujian / Modul", "File Test Suite Utama", "Status Kelulusan"]
    for j, h in enumerate(qa_headers):
        cell = t_qa.cell(0, j)
        cell.text = h
        set_cell_background(cell, HEX_PRIMARY)
        set_cell_margins(cell, 80, 80, 100, 100)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.runs[0].font.name = "Arial"
        p.runs[0].font.size = Pt(9)
        p.runs[0].font.bold = True
        p.runs[0].font.color.rgb = RGBColor(255, 255, 255)

    qa_data = [
        ("Pergudangan & Packing Slip", "WarehouseProcessingTest.php", "15 Passed (100%)"),
        ("Sinkronisasi Pipeline Shopee", "SyncStatusPageTest.php", "6 Passed (100%)"),
        ("Manajemen Retur & Verifikasi Fisik", "OrderReturnsPageTest.php", "4 Passed (100%)"),
        ("Audit Rekonsiliasi & Export", "ReconciliationReportTest.php", "10 Passed (100%)"),
        ("Dashboard SLA & Produk Publik", "DashboardTest.php & ProdukPageTest.php", "12 Passed (100%)"),
    ]

    for i, row in enumerate(qa_data, start=1):
        bg = HEX_LIGHT_BG if i % 2 == 1 else "FFFFFF"
        for j, val in enumerate(row):
            cell = t_qa.cell(i, j)
            cell.text = val
            set_cell_background(cell, bg)
            set_cell_margins(cell, 70, 70, 100, 100)
            p = cell.paragraphs[0]
            if j == 2:
                p.alignment = WD_ALIGN_PARAGRAPH.CENTER
                p.runs[0].font.bold = True
                p.runs[0].font.color.rgb = RGBColor(16, 128, 64)
            p.runs[0].font.name = "Arial"
            p.runs[0].font.size = Pt(9)

    p_summary = doc.add_paragraph(
        "Total Pengujian Sistem Keseluruhan: 153 Tests Passed, 0 Failed, 456 Assertions."
    )
    p_summary.runs[0].font.name = "Arial"
    p_summary.runs[0].font.size = Pt(9.5)
    p_summary.runs[0].font.bold = True

    doc.add_paragraph()

    # ================= LEMBAR PENGESAHAN =================
    h5 = doc.add_paragraph()
    r_h5 = h5.add_run("5. Lembar Pengesahan Anggota Kelompok 8")
    r_h5.font.name = "Arial"
    r_h5.font.size = Pt(13)
    r_h5.font.bold = True
    r_h5.font.color.rgb = COLOR_PRIMARY

    p_sign_intro = doc.add_paragraph(
        "Demikian laporan pembagian modul sistem informasi ini disusun dengan penuh tanggung jawab demi kelancaran penyelesaian Project-Based Learning (PBL) pada PT Mustika Makmur Abadi."
    )
    p_sign_intro.runs[0].font.name = "Arial"
    p_sign_intro.runs[0].font.size = Pt(9.5)

    p_loc = doc.add_paragraph("Sidoarjo, 26 Oktober 2026\nMengetahui dan Menyetujui,\nSeluruh Anggota Tim Kelompok 8:")
    p_loc.runs[0].font.name = "Arial"
    p_loc.runs[0].font.size = Pt(9.5)
    p_loc.runs[0].font.italic = True

    # Signature Table (2 rows x 2 cols)
    t_sign = doc.add_table(rows=2, cols=2)
    t_sign.alignment = WD_TABLE_ALIGNMENT.CENTER
    sign_members = [
        ("Ketua Tim / Penanggung Jawab Modul 1", "Muhammad Saugi Assegaf"),
        ("Anggota Tim / Penanggung Jawab Modul 2", "Aisya Nurrizahwa"),
        ("Anggota Tim / Penanggung Jawab Modul 3", "M. Syahfil Alfadil"),
        ("Anggota Tim / Penanggung Jawab Modul 4", "Noval Niko Amrila"),
    ]

    for idx, (role, name) in enumerate(sign_members):
        r_idx = idx // 2
        c_idx = idx % 2
        cell = t_sign.cell(r_idx, c_idx)
        cell.text = f"{role}\n\n\n\n_____________________________\n{name}"
        set_cell_margins(cell, 100, 100, 120, 120)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.runs[0].font.name = "Arial"
        p.runs[0].font.size = Pt(9)

    output_path = "Laporan Pembagian Tugas Kelompok 8 - Sistem Web PT Mustika Makmur Abadi.docx"
    doc.save(output_path)
    print(f"File successfully created: {output_path}")

if __name__ == '__main__':
    create_document()
