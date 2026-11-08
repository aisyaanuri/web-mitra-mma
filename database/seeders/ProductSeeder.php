<?php

namespace Database\Seeders;

use App\Enums\ProductCategory;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Real Yummy Bites catalog and authentic Rice products (Beras Jepang,
 * Beras Basmati, Beras Jasmin, Beras Organik, and Beras Lautan Mas),
 * sourced directly from yummybites.com and lautanmas.co.id.
 */
class ProductSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, slug: string, sku: string, source: string, weight: string, description: string, featured?: bool}>
     */
    private const YUMMY_BITES = [
        [
            'name' => 'Yummy Bites Meal Potato & Cheese (Step 1)',
            'slug' => 'yummy-bites-meal-potato-cheese-step-1',
            'sku' => 'YB-MEAL-POTCHEESE-1',
            'source' => 'MEAL/Yummy Bites 0019.png',
            'weight' => '145g',
            'description' => "Bubur MPASI siap santap resep rumahan untuk tahap awal Si Kecil mulai usia 6 bulan (Step 1). Dibuat dari kentang lembut berpadu keju cheddar gurih dan bahan segar alami pilihan melalui proses sterilisasi modern. Rendah natrium, tinggi Vitamin B1, serta sumber Vitamin D dan C. Bebas gluten dan tanpa bahan pengawet sintetik.\n\nKomposisi: Kentang, keju cheddar, kaldu alami. Alergen: Susu, keju.",
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites Meal Chicken Risotto with Cheese (Step 3)',
            'slug' => 'yummy-bites-meal-chicken-risotto-cheese-step-3',
            'sku' => 'YB-MEAL-CHIXRISOTTO-3',
            'source' => 'MEAL/Yummy Bites 0010.png',
            'weight' => '145g',
            'description' => "Risotto ayam dan keju bertekstur lebih kaya untuk Si Kecil usia 10 bulan ke atas (Step 3) yang sedang melatih kemampuan mengunyah dan mengeksplorasi rasa. Dibuat dengan resep rumahan dari daging ayam segar, keju gurih, dan beras pilihan tanpa pengawet sintetik maupun perasa buatan. Praktis, bergizi, dan higienis.\n\nKomposisi: Beras, daging ayam segar, keju cheddar, wortel, kaldu sayuran alami. Alergen: Susu, keju.",
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites Meal Salmon, Egg & Spinach (Step 3)',
            'slug' => 'yummy-bites-meal-salmon-egg-spinach-step-3',
            'sku' => 'YB-MEAL-SALMONEGG-3',
            'source' => 'MEAL/Yummy Bites 0001.png',
            'weight' => '145g',
            'description' => "Perpaduan ikan salmon segar pilihan, telur ayam, dan bayam kaya nutrisi untuk tahap MPASI lanjutan Si Kecil (10 bulan ke atas). Makanan siap santap bergizi tinggi yang kaya Omega 3, protein hewani, Vitamin K, dan Vitamin B3 untuk mengoptimalkan pertumbuhan otak dan fisik anak. Tanpa bahan pengawet, higienis dan praktis disajikan kapan saja.\n\nKomposisi: Beras, ikan salmon segar, telur, bayam, kaldu alami. Alergen: Ikan, telur.",
        ],
        [
            'name' => 'Yummy Bites Crunchies Chocolate Malt',
            'slug' => 'yummy-bites-crunchies-chocolate-malt',
            'sku' => 'YB-CRUNCH-CHOCOMALT',
            'source' => 'Crunchies-Choco1-edit.png',
            'weight' => '25g',
            'description' => "Snack stik panggang rasa cokelat malt lezat untuk anak usia 12 bulan ke atas. Dibuat dari jagung dan beras pilihan yang dipanggang (bukan digoreng), menghasilkan tekstur renyah lembut yang mudah lumer di mulut sehingga aman dari resiko tersedak. Bebas gluten (Gluten Free), rendah sodium, tanpa pemanis buatan, tanpa pengawet, serta diperkaya serat inulin dan kalsium karbonat.\n\nKomposisi: Jagung, beras, pati kentang, inulin, minyak kelapa, bubuk kakao, susu, bubuk malt, kalsium karbonat. Alergen: Susu, malt.",
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites Crunchies Cheese',
            'slug' => 'yummy-bites-crunchies-cheese',
            'sku' => 'YB-CRUNCH-CHEESE',
            'source' => 'Crunchies-Cheese2-edit.png',
            'weight' => '25g',
            'description' => "Snack stik jagung panggang rasa keju cheddar gurih alami untuk anak usia 1 tahun ke atas. Teksturnya renyah lembut dan langsung lumer di mulut Si Kecil. Bebas gluten, tanpa penguat rasa MSG sintetis, tanpa pengawet, serta diperkaya dengan serat inulin, kalsium, dan vitamin pendukung tumbuh kembang.\n\nKomposisi: Jagung, beras, pati kentang, inulin, minyak kelapa, bumbu keju cheddar (bubuk keju 30%, susu, premiks vitamin dan mineral), kalsium karbonat. Alergen: Susu, keju.",
        ],
        [
            'name' => 'Yummy Bites Crunchies Strawberry',
            'slug' => 'yummy-bites-crunchies-strawberry',
            'sku' => 'YB-CRUNCH-STROBERI',
            'source' => 'Crunchies-Stroberi2-edit.png',
            'weight' => '25g',
            'description' => "Snack stik jagung dan beras panggang rasa stroberi segar alami untuk anak usia 12 bulan ke atas. Tekstur renyah lembut yang lumer di lidah, membantu melatih motorik mandiri Si Kecil saat memegang makanan. Tanpa pemanis buatan, tanpa pengawet sintetik, bebas gluten, dan diperkaya kalsium serta serat pangan inulin.\n\nKomposisi: Jagung, beras, pati kentang, inulin, minyak kelapa, bubuk stroberi alami, kalsium karbonat, premiks vitamin.",
        ],
        [
            'name' => 'Yummy Bites BebeRoll Cheese',
            'slug' => 'yummy-bites-beberoll-cheese',
            'sku' => 'YB-ROLL-CHEESE',
            'source' => 'BEBEROLL/2-removebg-preview.png',
            'weight' => '40g',
            'description' => "Camilan roll beras panggang isi krim keju gurih khas Korea untuk Si Kecil mulai usia 9 bulan ke atas. Terbuat dari 8 macam serealia Korea pilihan (beras cokelat, beras putih, jagung, barley, ketan putih, kacang hitam, millet, dan beras hitam). Dipanggang bukan digoreng, bentuk silinder mudah digenggam untuk melatih motorik Si Kecil. Tinggi kalsium, fosfor, Vitamin E dan B1. Tanpa pewarna maupun pengawet buatan.\n\nKomposisi: Serealia Korea 55,089%, minyak kelapa sawit, bubuk keju 15%, bubuk susu skim, tokoferol, kalsium karbonat, kuning telur. Alergen: Susu, telur.",
        ],
        [
            'name' => 'Yummy Bites BebeRoll Blueberry',
            'slug' => 'yummy-bites-beberoll-blueberry',
            'sku' => 'YB-ROLL-BLUEBERRY',
            'source' => 'BEBEROLL/3-removebg-preview.png',
            'weight' => '40g',
            'description' => "Camilan roll beras panggang isi krim blueberry manis segar alami untuk anak mulai usia 9 bulan. Dibuat dari kebaikan 8 macam biji-bijian Korea berkualitas tinggi yang dipanggang oven. Mudah digenggam tangan mungil anak, tinggi Vitamin E, Vitamin B1, kalsium, dan fosfor. Bebas pengawet, bebas pewarna buatan, dan tanpa perasa sintetis.\n\nKomposisi: Serealia Korea 55,089%, bubuk blueberry 15%, minyak kelapa sawit, bubuk susu skim, tokoferol alami, kalsium karbonat. Alergen: Susu, telur.",
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites BebeRoll Strawberry',
            'slug' => 'yummy-bites-beberoll-strawberry',
            'sku' => 'YB-ROLL-STROBERI',
            'source' => 'BEBEROLL/4-removebg-preview.png',
            'weight' => '40g',
            'description' => "Camilan roll beras panggang isi krim stroberi lezat untuk Si Kecil usia 9 bulan ke atas. Mengandung 8 macam padi-padian bergizi asal Korea yang diproses secara higienis melalui pemanggangan. Bentuk roll praktis membantu melatih koordinasi tangan dan mulut anak. Kaya akan kalsium, fosfor, dan Vitamin E alami untuk mendukung pertumbuhan tulang dan gigi.\n\nKomposisi: Serealia Korea 55,089%, bubuk stroberi 15%, minyak kelapa sawit, bubuk susu skim, tokoferol, kalsium karbonat. Alergen: Susu, telur.",
        ],
        [
            'name' => 'Yummy Bites Free Range Chicken Broth',
            'slug' => 'yummy-bites-free-range-chicken-broth',
            'sku' => 'YB-KALDU-AYAM',
            'source' => 'CHICKEN.png',
            'weight' => '50g',
            'description' => "Kaldu bubuk alami MPASI yang diolah dari daging ayam kampung (free range chicken) asli pilihan untuk bayi usia 6 bulan ke atas. 100% bahan alami food-grade tanpa tambahan MSG sintetis, tanpa bahan pengawet, tanpa pewarna buatan, serta tanpa tambahan gula dan garam berlebih. Tekstur bubuk halus yang cepat larut, menjadi solusi tepat penambah cita rasa gurih alami untuk anak yang sedang fase GTM (Gerakan Tutup Mulut).\n\nKomposisi: Daging ayam kampung asli segar, ekstrak sayuran alami, antioksidan tokoferol alami. Tanpa pengawet dan tanpa MSG.",
        ],
        [
            'name' => 'Yummy Bites Beef Broth',
            'slug' => 'yummy-bites-beef-broth',
            'sku' => 'YB-KALDU-SAPI',
            'source' => 'BEEF.png',
            'weight' => '50g',
            'description' => "Kaldu bubuk MPASI alami dari sari daging sapi pilihan berkualitas tinggi untuk anak mulai usia 6 bulan. Diproses secara higienis dengan teknologi modern untuk mempertahankan nutrisi asli daging sapi. Tanpa penguat rasa buatan (No MSG), tanpa pengawet sintetik, dan tanpa pewarna. Menambah kelezatan alami pada bubur MPASI, sup, semur, tumisan sayur, maupun aneka olahan pasta anak.\n\nKomposisi: Ekstrak daging sapi pilihan, kaldu sayuran segar alami, antioksidan tokoferol alami.",
        ],
        [
            'name' => 'Yummy Bites Cheese Broth',
            'slug' => 'yummy-bites-cheese-broth',
            'sku' => 'YB-KALDU-KEJU',
            'source' => 'CHEESE.png',
            'weight' => '50g',
            'description' => "Kaldu bubuk rasa keju gurih alami untuk memperkaya variasi menu MPASI Si Kecil mulai usia 6 bulan. Terbuat dari keju berkualitas tanpa MSG, tanpa pewarna, dan tanpa pengawet kimiawi. Praktis ditaburkan langsung atau dicampurkan ke dalam bubur hangat, pasta, mashed potato, maupun sup krim kesukaan si kecil.\n\nKomposisi: Bubuk keju alami berkualitas, ekstrak susu alami, mineral kalsium. Alergen: Susu, keju.",
        ],
        [
            'name' => 'Yummy Bites Wild Forest Honey',
            'slug' => 'yummy-bites-wild-forest-honey',
            'sku' => 'YB-MADU-FOREST',
            'source' => 'WFH 5.png',
            'weight' => '275g',
            'description' => "100% madu hutan murni (raw & pure honey) yang dipanen langsung dari nektar bunga liar alami di hutan untuk anak usia 1 tahun ke atas dan seluruh keluarga. Tanpa campuran pemanis buatan, tanpa sirup gula tambahan, dan tanpa bahan pengawet. Kaya enzim aktif, flavonoid alami, dan antioksidan untuk memperkuat daya tahan tubuh serta menjaga vitalitas harian. Dikemas dalam toples kaca premium higienis dan bersertifikat Halal.\n\nKomposisi: 100% madu hutan murni alami. Saran konsumsi: 1-2 sendok teh per hari langsung atau dicampur ke minuman dan makanan.",
        ],
        [
            'name' => 'Yummy Bites Black Elderberry Honey',
            'slug' => 'yummy-bites-black-elderberry-honey',
            'sku' => 'YB-MADU-ELDERBERRY',
            'source' => 'BEH 5.png',
            'weight' => '275g',
            'description' => "Perpaduan madu murni alami dengan ekstrak buah Black Elderberry asli untuk anak usia 12 bulan ke atas dan dewasa. Black elderberry kaya akan antosianin dan vitamin C tinggi yang terbukti efektif memperkuat sistem imun tubuh, terutama saat cuaca tidak menentu atau musim pancaroba. Memiliki sensasi rasa manis berpadu segar alami buah beri yang disukai anak. Bebas pengawet, bebas pemanis sintetis, dan dikemas dalam botol kaca ramah lingkungan.\n\nKomposisi: Madu murni alami, ekstrak buah black elderberry alami. Halal resmi.",
        ],
        [
            'name' => 'Yummy Bites Purée Blueberry, Banana & Apple',
            'slug' => 'yummy-bites-puree-blueberry-banana-apple',
            'sku' => 'YB-PUREE-BBA',
            'source' => 'BBA 1.png',
            'weight' => '100g',
            'description' => "100% puree buah segar murni perpaduan pisang (50%), blueberry (30%), dan apel (20%) standar baby-grade untuk bayi usia 6 bulan ke atas. Teksturnya sangat lembut dan halus tanpa potongan kasar, aman dan mudah ditelan bayi yang baru memulai MPASI. Tanpa tambahan gula (manis alami buah), tanpa garam (low sodium), bebas pengawet, bebas gluten, dan bebas alergen. Kaya antioksidan dari blueberry serta sumber serat, Vitamin B6, B9, dan kalium dalam kemasan pouch praktis siap santap.\n\nKomposisi: Puree pisang 50%, puree blueberry 30%, puree apel 20%, konsentrat jus lemon. Allergen Free & Gluten Free.",
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites Purée Strawberry, Guava & Banana',
            'slug' => 'yummy-bites-puree-strawberry-guava-banana',
            'sku' => 'YB-PUREE-SGB',
            'source' => 'SGB 1.png',
            'weight' => '100g',
            'description' => "Puree buah alami dari kombinasi stroberi segar, jambu biji merah kaya vitamin, dan pisang matang lembut untuk bayi usia 6 bulan ke atas. Diproses secara steril tanpa gula tambahan, tanpa perasa sintetis, dan tanpa bahan pengawet. Menjadi sumber serat alami, Vitamin C, Vitamin B6, dan folat untuk mendukung daya tahan tubuh serta melancarkan pencernaan Si Kecil. Kemasan pouch higienis yang praktis dibawa bepergian.\n\nKomposisi: Puree buah stroberi segar, puree jambu merah, puree pisang, konsentrat jus lemon alami. Bebas gluten dan bebas bahan kimia pengawet.",
        ],
        [
            'name' => 'Yummy Bites Furikake Chicken',
            'slug' => 'yummy-bites-furikake-chicken',
            'sku' => 'YB-FURI-AYAM',
            'source' => 'TAP09881.png',
            'weight' => '35g',
            'description' => "Taburan rumput laut nori dan bumbu ayam gurih lezat khas Korea untuk anak usia 1 tahun ke atas. Diperkaya dengan DHA & EPA, asam lemak Omega 3 & 6, serta tinggi Kalsium, Vitamin D, dan Vitamin B1 untuk mendukung nutrisi otak dan pertumbuhan tulang anak. Sangat praktis ditaburkan di atas nasi hangat, bubur, sup, atau dibuat bola-bola nasi kepal (onigiri). Tanpa pengawet sintetik, tanpa pemanis buatan, dan tanpa pewarna berbahaya.\n\nKomposisi: Rumput laut (nori), bumbu ayam alami, biji wijen, garam laut, kalsium, DHA & EPA. Alergen: Telur, kedelai, laktosa.",
        ],
        [
            'name' => 'Yummy Bites Furikake Seafood',
            'slug' => 'yummy-bites-furikake-seafood',
            'sku' => 'YB-FURI-SEAFOOD',
            'source' => 'TAP09874.png',
            'weight' => '35g',
            'description' => "Taburan rumput laut nori rasa aneka hidangan laut (seafood) gurih alami untuk anak usia 12 bulan ke atas. Produk berkualitas asal Korea yang mengandung kebaikan DHA & EPA, Kalsium, Vitamin D, dan Vitamin B1. Membantu meningkatkan selera makan Si Kecil yang sedang bosan dengan menu harian. Tanpa pengawet sintetis, tanpa pemanis buatan, dan bebas pewarna buatan.\n\nKomposisi: Rumput laut nori, bubuk udang dan ikan alami, biji wijen, garam laut alami, kalsium karbonat, DHA & EPA. Alergen: Ikan, udang/krustasea, wijen.",
        ],
        [
            'name' => 'Yummy Bites Crackers Blueberry',
            'slug' => 'yummy-bites-crackers-blueberry',
            'sku' => 'YB-CRACKER-BLUEBERRY',
            'source' => 'GSP_8756.png',
            'weight' => '50g',
            'description' => "Biskuit beras renyah rasa blueberry segar alami untuk bayi usia 6 bulan ke atas. Memiliki tekstur unik yang langsung lumer seketika di mulut saat terkena air liur sehingga sangat aman dari resiko tersedak. Allergen Free (bebas jagung, susu, gandum, telur, kacang, ikan, kedelai, kerang, wijen), bebas gluten, bersertifikasi Halal, tanpa pengawet, dan tanpa perasa buatan. Berisi 12 bungkus praktis dengan total 24 keping biskuit berbentuk oval ramah genggaman tangan bayi.\n\nKomposisi: Tepung beras, pati kentang, gula tebu, garam laut alami, bubuk blueberry alami.",
        ],
        [
            'name' => 'Yummy Bites Crackers Vegetables',
            'slug' => 'yummy-bites-crackers-vegetables',
            'sku' => 'YB-CRACKER-SAYUR',
            'source' => 'GSP_8751 rev.png',
            'weight' => '50g',
            'description' => "Biskuit beras renyah rasa sayuran alami (labu kuning, wortel, bayam, kol) untuk bayi mulai usia 6 bulan. Tekstur lembut yang mudah larut di mulut, memperkenalkan aneka rasa sayuran sehat sejak dini secara menyenangkan. Bebas alergen utama (Allergen Free), bebas gluten, halal, tanpa pewarna sintetis, dan tanpa bahan pengawet. Kemasan higienis 12 sachet individual isi 24 keping biskuit.\n\nKomposisi: Tepung beras, pati kentang, labu bubuk (0,33%), wortel bubuk (0,26%), bayam bubuk (0,25%), kol bubuk (0,21%), garam laut alami.",
        ],
    ];

    /**
     * @var array<int, array{name: string, slug: string, sku: string, source: string, weight: string, country: string, description: string, featured?: bool}>
     */
    private const BERAS_PRODUCTS = [
        // BERAS JEPANG
        [
            'name' => 'Koshikari 5 Kg',
            'slug' => 'koshikari-5-kg',
            'sku' => 'BJ-KOSHI-5KG',
            'source' => 'Koshihikari Premium 5KG.png',
            'weight' => '5kg',
            'country' => 'Jepang',
            'description' => "Beras Jepang Koshihikari super premium mutu tertinggi kemasan 5 kg dari Lautan Mas. Dikenal luas oleh restoran Jepang terkemuka dan pecinta kuliner otentik karena bulir berasnya yang pendek-bulat (short grain), bersih berkilau, dengan tekstur super pulen, lembut, dan daya lekat alami yang sempurna tanpa perlu tambahan ketan.\n\nRekomendasi Sajian: Sushi roll, Onigiri, Bento box, Donburi, Katsu rice, dan santapan keluarga premium.\nTekstur: Bulat utuh (Japonica), sangat pulen, dan lengket alami.",
            'featured' => true,
        ],
        [
            'name' => 'Koshikari 2 Kg',
            'slug' => 'koshikari-2-kg',
            'sku' => 'BJ-KOSHI-2KG',
            'source' => 'koshi 2.jpg',
            'weight' => '2kg',
            'country' => 'Jepang',
            'description' => "Beras Jepang varietas Koshihikari kemasan 2 kg praktis dengan teknologi standing pouch zip-lock kedap udara dari Lautan Mas. Memudahkan penyimpanan harian tanpa khawatir bulir beras lembap atau berkutu. Menghasilkan nasi dengan aroma manis alami, rasa gurih khas, dan tekstur pulen kenyal yang lezat bahkan saat sudah dingin.\n\nRekomendasi Sajian: Sushi, onigiri, bento harian, hidangan teppanyaki.\nTekstur: Bulat, pulen kenyal, dan daya rekat ideal.",
        ],
        [
            'name' => 'Hanaro 5 Kg',
            'slug' => 'hanaro-5-kg',
            'sku' => 'BJ-HANARO-5KG',
            'source' => 'Hanaro 5KG.png',
            'weight' => '5kg',
            'country' => 'Jepang',
            'description' => "Beras Japonica premium varietas Hanaro kemasan 5 kg dari Lautan Mas. Memiliki bulir beras bulat putih berkilau dengan tingkat kelengketan yang sangat pas dan rasa manis gurih alami. Sangat digemari untuk aneka sajian masakan khas Korea dan Jepang karena teksturnya yang empuk serta menyerap bumbu dengan sangat baik.\n\nRekomendasi Sajian: Kimbap, Gimbap, Bibimbap, Jumeokbap (nasi kepal Korea), dan masakan Asia Timur.\nTekstur: Bulat, super pulen, berkilau, dan tingkat kelengketan pas.",
        ],

        // BERAS BASMATI
        [
            'name' => 'Lai Qila Diabetes 5 Kg',
            'slug' => 'lai-qila-diabetes-5-kg',
            'sku' => 'BB-LAIQILA-DIAB-5KG',
            'source' => 'Lal Qilla Diabetes 5KG.png',
            'weight' => '5kg',
            'country' => 'India',
            'description' => "Beras Basmati Lal Qilla khusus ramah diabetes kemasan 5 kg dengan sertifikasi Indeks Glikemik Rendah (Low G.I.). Berasal dari varietas basmati unggul pegunungan Himalaya yang telah melalui proses aging dan pengolahan khusus sehingga pelepasan glukosa ke dalam darah berjalan lambat dan stabil. Menjadi pilihan utama penderita diabetes, penderita obesitas, serta pelaku gaya hidup sehat rendah gula.\n\nRekomendasi Sajian: Nasi Biryani Sehat, Nasi Kebuli, Nasi Mandhi, Pilaf, atau pengganti nasi putih harian.\nTekstur: Bulir ekstra panjang, ramping, terurai mekar sempurna saat dimasak (tidak menggumpal), beraroma rempah alami khas.",
            'featured' => true,
        ],
        [
            'name' => 'Punjab 5 Kg',
            'slug' => 'punjab-5-kg',
            'sku' => 'BB-PUNJAB-5KG',
            'source' => 'Punjab 5KG Basmati Rice -Depan.png',
            'weight' => '5kg',
            'country' => 'India',
            'description' => "Beras Basmati India varietas 1121 kualitas nomor satu (Punjab King / Lal Qilla Sella) kemasan karung goni eksklusif 5 kg dari Lautan Mas. Melalui proses parboiled (kukus sella) sebelum penggilingan yang mengunci nutrisi bulir beras di dalamnya. Bulir beras dapat memanjang hingga dua kali lipat saat dimasak tanpa patah atau hancur.\n\nRekomendasi Sajian: Authentic Indian & Middle Eastern Cuisines (Nasi Biryani, Kebuli, Mandhi, Kabsah, Bukhari).\nTekstur: Bulir ekstra panjang, halus, kokoh, mekar sempurna, dan tidak mudah patah.",
        ],

        // BERAS JASMIN
        [
            'name' => 'Jasmine Bangkok 5 Kg',
            'slug' => 'jasmine-bangkok-5-kg',
            'sku' => 'BJAS-BANGKOK-5KG',
            'source' => 'LM Jasmine Bangkok 5KG.png',
            'weight' => '5kg',
            'country' => 'Thailand',
            'description' => "Beras wangi khusus Jasmine Bangkok kualitas ekspor AAA kemasan 5 kg dari Lautan Mas. Memiliki bulir beras panjang (long grain) utuh dengan keharuman melati alami tanpa pewangi sintetis dan tanpa zat pemutih kimiawi. Menghasilkan nasi putih bersih yang empuk, lembut, pulen, dan harum semerbak.\n\nRekomendasi Sajian: Nasi Hainan, Nasi Uduk Gurih, masakan tumisan Thailand, dan aneka masakan restoran bintang lima.\nTekstur: Bulir panjang utuh, lembut, pulen, dan wangi alami.",
        ],
        [
            'name' => 'Leon Thai Jasmine 5 Kg',
            'slug' => 'leon-thai-jasmine-5-kg',
            'sku' => 'BJAS-LEON-5KG',
            'source' => 'Leon Thai Jasmine Rice 5KG.png',
            'weight' => '5kg',
            'country' => 'Thailand',
            'description' => "Beras melati asli Thailand varietas unggul Thai Hom Mali terbaik kualitas AAA kemasan 5 kg dari brand ternama Leon (Lautan Mas). Beras pilihan dengan derajat sosoh tinggi, menghasilkan nasi putih bersih mengkilap dengan aroma wangi melati alami yang menggoda serta tekstur pulen lembut istimewa.\n\nRekomendasi Sajian: Santapan harian keluarga berkelas, sajian seafood, dan masakan oriental.\nTekstur: Bulir panjang, sangat empuk, pulen, dan beraroma harum memikat.",
            'featured' => true,
        ],
        [
            'name' => 'Leon Thai Jasmin 2 Kg',
            'slug' => 'leon-thai-jasmin-2-kg',
            'sku' => 'BJAS-LEON-2KG',
            'source' => 'Leon Thai Jasmine Merah 5KG.png',
            'weight' => '2kg',
            'country' => 'Thailand',
            'description' => "Beras melati Thailand kualitas AAA kemasan praktis 2 kg dari Leon. Sangat ideal untuk kebutuhan harian keluarga modern yang mengutamakan cita rasa nasi pulen berkualitas tinggi dengan aroma wangi alami yang segar.\n\nRekomendasi Sajian: Menu harian keluarga, nasi liwet wangi, hidangan ayam bakar.\nTekstur: Bulir panjang bersih, pulen lembut, dan harum melati alami.",
        ],

        // BERAS ORGANIK
        [
            'name' => 'Hitam Organik 2 Kg',
            'slug' => 'hitam-organik-2-kg',
            'sku' => 'BO-HITAM-2KG',
            'source' => 'OL Hitam 2KG.png',
            'weight' => '2kg',
            'country' => 'Indonesia',
            'description' => "Beras hitam organik varietas lokal pilihan Organic Life kemasan standing pouch 2 kg. Dibudidayakan secara alami di lahan pertanian ramah lingkungan hanya dengan kompos dan pupuk kandang alami tanpa pestisida kimia, serta telah mengantongi sertifikat organik resmi dari lembaga sertifikasi yang diakui pemerintah Indonesia. Kaya akan antosianin (antioksidan pelindung sel tubuh), zat besi, dan serat alami tinggi.\n\nRekomendasi Sajian: Nasi hitam antioksidan, campuran beras putih untuk diet sehat, bubur beras hitam bernutrisi.\nTekstur: Bulir utuh ungu gelap pekat alami, tekstur kenyal legit berserat dengan rasa gurih khas.",
        ],
        [
            'name' => 'Merah Organik 2 Kg',
            'slug' => 'merah-organik-2-kg',
            'sku' => 'BO-MERAH-2KG',
            'source' => 'OL Merah 2KG.png',
            'weight' => '2kg',
            'country' => 'Indonesia',
            'description' => "Beras merah organik Organic Life kemasan standing pouch 2 kg bersertifikat organik resmi Indonesia. Diproses dengan mempertahankan lapisan aleuron (bekatul) alami yang kaya akan vitamin B kompleks, mineral magnesium, fosfor, dan serat pangan larut. Membantu menjaga kadar kolesterol, melancarkan sistem pencernaan, serta memberi rasa kenyang lebih tahan lama bagi yang menjalani program diet atau pola makan sehat.\n\nRekomendasi Sajian: Menu harian diet sehat, nasi merah bakar rempah, pendamping sayur bening dan lauk pauk.\nTekstur: Berserat kaya nutrisi, pulen empuk saat dimasak dengan takaran air yang pas.",
        ],
        [
            'name' => 'Kombinasi Organik 2 Kg',
            'slug' => 'kombinasi-organik-2-kg',
            'sku' => 'BO-KOMBINASI-2KG',
            'source' => 'OL Kombinasi 2KG.png',
            'weight' => '2kg',
            'country' => 'Indonesia',
            'description' => "Perpaduan harmonis dan seimbang 3 varietas beras organik unggulan: Beras Putih, Beras Merah, dan Beras Hitam Organik dari Organic Life dalam satu kemasan pouch 2 kg. Menghadirkan keseimbangan rasa pulen lembut dari beras putih dan kekayaan serat serta antioksidan tinggi dari beras merah dan hitam. Bersertifikat resmi bebas pestisida dan bahan kimia sintetis.\n\nRekomendasi Sajian: Nasi campur sehat harian keluarga, sajian bento warna-warni alami untuk anak-anak.\nTekstur: Perpaduan unik pulen lembut dan kenyal legit yang lezat.",
            'featured' => true,
        ],
        [
            'name' => 'Fragrant Organic 2 Kg',
            'slug' => 'fragrant-organic-2-kg',
            'sku' => 'BO-FRAGRANT-2KG',
            'source' => 'OL Pandan Wangi 2KG.png',
            'weight' => '2kg',
            'country' => 'Indonesia',
            'description' => "Beras pandan wangi organik pilihan Organic Life kemasan 2 kg. Ditanam menggunakan metode pertanian organik berkelanjutan bersertifikat tanpa pemutih, tanpa pengawet, dan tanpa esens pewangi buatan. Mengeluarkan aroma harum pandan segar alami saat ditanak dengan tekstur nasi yang empuk, pulen, dan tidak cepat mengering atau basi.\n\nRekomendasi Sajian: Nasi liwet wangi tradisional, tumpeng syukuran, dan sajian nasi putih istimewa keluarga.\nTekstur: Bulir beras bersih, pulen empuk, dan aroma pandan wangi alami.",
        ],
        [
            'name' => 'Sentra Ramos 2 Kg',
            'slug' => 'sentra-ramos-2-kg',
            'sku' => 'BO-SENTRA-RAMOS-2KG',
            'source' => 'OL Sentra Ramos 2KG.png',
            'weight' => '2kg',
            'country' => 'Indonesia',
            'description' => "Beras varietas Sentra Ramos (Setra Ramos) organik murni dari Organic Life kemasan 2 kg. Menjadi varietas beras favorit keluarga Indonesia karena bulirnya yang panjang bersih, cita rasa manis gurih alami, dan tekstur pulen yang sangat cocok mendampingi aneka lauk masakan Nusantara. Diproduksi secara higienis dan bersertifikat organik resmi.\n\nRekomendasi Sajian: Nasi putih harian keluarga, nasi goreng Nusantara, pendamping lauk rendang dan sayur lodeh.\nTekstur: Bulir panjang, bersih berkilau, pulen tidak mudah hancur.",
        ],

        // BERAS LAUTAN MAS
        [
            'name' => 'Beras Merah 2 Kg',
            'slug' => 'beras-merah-2-kg',
            'sku' => 'BLM-MERAH-2KG',
            'source' => 'beras merah specialll.png',
            'weight' => '2kg',
            'country' => 'Indonesia',
            'description' => "Beras merah spesial Lautan Mas kemasan 2 kg adalah beras merah pilihan berkualitas tinggi yang kaya akan nutrisi dan serat alami. Merupakan sumber karbohidrat kompleks rendah kalori, tinggi serat pangan, bebas gluten, dan bebas lemak trans berbahaya. Sangat dianjurkan bagi pelaku hidup sehat, kebugaran fisik, dan pencegahan risiko diabetes.\n\nRekomendasi Sajian: Nasi merah harian pelaku pola hidup sehat, gym & fitness diet, menu ramah lansia.\nTekstur: Tekstur berserat tinggi, rasa gurih nutty alami, padat mengenyangkan lebih lama.",
        ],
    ];

    public function run(): void
    {
        $yummyRoot = base_path('ALL PRODUK');
        $berasRoot = base_path('FOTO PRODUK BERAS');
        $targetDir = 'products';
        File::ensureDirectoryExists(storage_path('app/public/'.$targetDir));

        // Remove legacy placeholder rice records if present
        Product::whereIn('sku', [
            'BERAS-JEPANG', 'BERAS-KOREA', 'BERAS-INDIA', 'BERAS-VIETNAM', 'BERAS-THAILAND',
        ])->delete();

        foreach (self::YUMMY_BITES as $item) {
            $product = Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'category' => ProductCategory::YummyBites,
                    'country_of_origin' => null,
                    'description' => $item['description'],
                    'weight' => $item['weight'],
                    'is_active' => true,
                    'is_featured' => $item['featured'] ?? false,
                    'meta_title' => $item['name'],
                    'meta_description' => mb_substr(str_replace("\n", ' ', $item['description']), 0, 160),
                ]
            );

            $sourcePath = $yummyRoot.'/'.$item['source'];
            $webpPath = $targetDir.'/'.$item['slug'].'.webp';
            $storedWebpPath = storage_path('app/public/'.$webpPath);

            if (! File::exists($sourcePath) && File::exists($storedWebpPath)) {
                $sourcePath = $storedWebpPath;
            }
            if (! File::exists($sourcePath)) {
                continue;
            }

            $storedPath = null;

            if (File::exists($storedWebpPath)) {
                $storedPath = $webpPath;
            } else {
                $extension = File::extension($sourcePath);
                $storedPath = $targetDir.'/'.$item['slug'].'.'.$extension;
                File::copy($sourcePath, storage_path('app/public/'.$storedPath));
            }

            if ($storedPath) {
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'sort_order' => 0],
                    ['image_path' => $storedPath, 'alt_text' => $item['name']]
                );
            }
        }

        foreach (self::BERAS_PRODUCTS as $item) {
            $product = Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'category' => ProductCategory::BerasImpor,
                    'country_of_origin' => $item['country'],
                    'description' => $item['description'],
                    'weight' => $item['weight'],
                    'is_active' => true,
                    'is_featured' => $item['featured'] ?? false,
                    'meta_title' => $item['name'],
                    'meta_description' => mb_substr(str_replace("\n", ' ', $item['description']), 0, 160),
                ]
            );

            $sourcePath = $berasRoot.'/'.$item['source'];
            $webpPath = $targetDir.'/'.$item['slug'].'.webp';
            $storedWebpPath = storage_path('app/public/'.$webpPath);

            if (! File::exists($sourcePath) && File::exists($storedWebpPath)) {
                $sourcePath = $storedWebpPath;
            }
            if (! File::exists($sourcePath)) {
                continue;
            }

            $storedPath = null;
            if (File::exists($storedWebpPath)) {
                $storedPath = $webpPath;
            } else {
                $extension = File::extension($sourcePath);
                $storedPath = $targetDir.'/'.$item['slug'].'.'.$extension;
                File::copy($sourcePath, storage_path('app/public/'.$storedPath));
            }

            if ($storedPath) {
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'sort_order' => 0],
                    ['image_path' => $storedPath, 'alt_text' => $item['name']]
                );
            }
        }
    }
}
