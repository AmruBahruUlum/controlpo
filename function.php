<?php  
session_start();
// membuat koneksi kedatabase
$conn = mysqli_connect("localhost", "root", "", "controlpo");

// menambah barang baru
if(isset($_POST['addnewbarang'])){
    $no_po = $_POST['no_po'];
    $namabarang = $_POST['nama_barang'];
    $nama_perusahaan = $_POST['nama_perusahaan'];
    $stok = $_POST['stok'];

    
    $addtotable = mysqli_query($conn, "INSERT INTO stock (no_po, namabarang, nama_perusahaan, stok) VALUES ('$no_po', '$namabarang', '$nama_perusahaan', '$stok')");

    if($addtotable){
        header('location:index.php');
    } 
}
if (isset($_POST['barangmasuk'])) {
    $barangnya = $_POST['barangnya'];           
    $no_po = $_POST['no_po'];                  
    $nama_perusahaan = $_POST['nama_perusahaan'];
    $qty = $_POST['qty'];

    // Ambil stok saat ini dari tabel stock berdasarkan id_barang
    $cekstoksekarang = mysqli_query($conn,"SELECT * FROM stock WHERE id_barang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstoksekarang);

    // Pastikan kolom 'stok' ada dan ambil nilainya
    if (isset($ambildatanya['stok'])) {
        $stoksekarang = $ambildatanya['stok'];
        $tambahkanstoksekarangdenganquantity = $stoksekarang + $qty;

        // Query untuk memasukkan data ke tabel masuk (hanya satu item yang sesuai dengan id_barang yang diinput)
        $addtomasuk = mysqli_query($conn, "INSERT INTO masuk (no_po, id_barang, nama_perusahaan, qty) VALUES ('$no_po','$barangnya', '$nama_perusahaan','$qty')");
        
        // Perbarui stok barang yang sesuai berdasarkan id_barang
        $updatestokmasuk = mysqli_query($conn, "UPDATE stock SET stok='$tambahkanstoksekarangdenganquantity' WHERE id_barang='$barangnya'");

        // Cek apakah kedua query berhasil
        if ($addtomasuk && $updatestokmasuk) {
            header('location:index.php');
            exit(); // Pastikan skrip berhenti setelah pengalihan
        } else {
            echo 'Gagal: ' . mysqli_error($conn);
        }
    } else {
        echo 'Gagal: Kolom stok tidak ditemukan.';
    }
}

//barang keluar
if (isset($_POST['barangkeluar'])) {
    $barangnya = $_POST['barangnya'];                         
    $pengirim = $_POST['pengirim'];
    $qty = $_POST['qty'];

    // Ambil stok saat ini
    $cekstoksekarang = mysqli_query($conn,"SELECT * FROM stock WHERE id_barang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstoksekarang);

    // Pastikan kolom 'stok' ada dan ambil nilainya
    if (isset($ambildatanya['stok'])) {
        $stoksekarang = $ambildatanya['stok'];

        if($stoksekarang>=$qty){
        $tambahkanstoksekarangdenganquantity = $stoksekarang - $qty;

        // Query untuk memasukkan data ke tabel masuk
        $addtokeluar = mysqli_query($conn, "INSERT INTO keluar ( id_barang, pengirim, qty) VALUES ('$barangnya', '$pengirim','$qty')");
        
        // Perbarui stok
        $updatestokmasuk = mysqli_query($conn, "UPDATE stock SET stok='$tambahkanstoksekarangdenganquantity' WHERE id_barang='$barangnya'");

        if ($addtokeluar&&$updatestokmasuk) {
            header('location:index.php');
            exit();
        } else {
            echo 'Gagal: ' . mysqli_error($conn);
        }
    } else {
   
        echo '
        <script>
        alert("Stok Barang Sudah Close");
        window.location.href="keluar.php";
        </script>
        ';
}

}
}


  // Update Info Barang

  if(isset($_POST['updatebarang'])){
    $idb =$_POST['idb'];
    $no_po = $_POST['no_po'];
    $nama_perusahaan = $_POST['nama_perusahaan'];
    $stok = $_POST['stok'];
    $update =mysqli_query($conn,"update stock set no_po='$no_po',nama_perusahaan='$nama_perusahaan', stok='$stok' where id_barang='$idb'");
   if($update){
    header('location:index.php');
} else {
    echo 'Gagal: ' . mysqli_error($conn);
}
} 
   // menghapus barang dari stock

   if(isset($_POST['hapusbarang'])){
    $idb =$_POST['idb'];

    $hapus = mysqli_query($conn,"Delete from stock where id_barang='$idb'");

    if($hapus){
        header('location:index.php');
    } else {
        echo 'Gagal: ' . mysqli_error($conn);
    }
    } 

    // edit barang masuk
    if(isset($_POST['updatebarangmasuk'])) {
        $idb = $_POST['idb'];    // ID barang (stok item)
        $idm = $_POST['idm'];    // Ganti dengan nama yang benar berdasarkan kolom sebenarnya (misalnya id)
        $no_po = $_POST['no_po']; 
        $qty = $_POST['qty'];
    
        // Ambil detail stok saat ini
        $lihatstok = mysqli_query($conn,"SELECT * FROM stock WHERE id_barang='$idb'");
        $stoknya = mysqli_fetch_array($lihatstok);
        if (isset($stoknya['stok'])) {
            $stokskrng = $stoknya['stok'];
        } else {
            echo "Gagal: Kolom stok tidak ditemukan.";
            exit;
        }
    
        // Ambil qty barang masuk saat ini
        $qtyskrng_query = mysqli_query($conn, "SELECT qty FROM masuk WHERE idmasuk='$idm'");
        $qtynya = mysqli_fetch_array($qtyskrng_query);
        if (isset($qtynya['qty'])) {
            $qtyskrng = $qtynya['qty'];
        } else {
            echo "Gagal: Kolom qty tidak ditemukan.";
            exit;
        }
    
        if($qty>$qtyskrng) {
            $selisih = $qty - $qtyskrng;   // Selisih antara qty baru dan lama
            $kurangin = $stokskrng + $selisih; // Perbarui stok sesuai dengan selisih
    
            // Perbarui tabel stok dan tabel masuk
            $kuranginstoknya = mysqli_query($conn, "UPDATE stock SET stok='$kurangin' WHERE id_barang='$idb'");
            $updatenya = mysqli_query($conn, "UPDATE masuk SET qty='$qty', no_po='$no_po' WHERE idmasuk='$idm'");
    
            if($kuranginstoknya && $updatenya) {
                header('location:masuk.php');
            } else {
                echo 'Gagal: ' . mysqli_error($conn);
            }
        } else {
            $selisih = $qtyskrng - $qty;
            $kurangin = $stokskrng - $selisih;
    
            $kuranginstoknya = mysqli_query($conn, "UPDATE stock SET stok='$kurangin' WHERE id_barang='$idb'");
            $updatenya = mysqli_query($conn, "UPDATE masuk SET qty='$qty', no_po='$no_po' WHERE idmasuk='$idm'");
    
            if ($kuranginstoknya && $updatenya) {
                header('location:masuk.php');
            } else {
                echo 'Gagal: ' . mysqli_error($conn);
            }
        }
    }
    
    // hapus barang masuk

    if(isset($_POST['hapusbarangmasuk'])){
        $idb = $_POST['idb'];
        $qty = $_POST['kty'];
        $idm = $_POST['idm'];
        
        $getdatastok = mysqli_query($conn,"select * from stock where id_barang='$idb'");
        $data = mysqli_fetch_array($getdatastok);
        $stok = $data['stok'];

        $selisih = $stok-$qty;

        $update = mysqli_query($conn,"update stock set stok='$selisih' where id_barang='$idb'");
        $hapusdata = mysqli_query($conn,"delete from masuk where idmasuk='$idm'");

        if($update&&$hapusdata){
            header('location:masuk.php');

        }  else {
            header('location:masuk.php');
        }
    }
           
    // Mengubah data keluar
    if(isset($_POST['updatebarangkeluar'])) {
        $idb = $_POST['idb'];
        $idk = $_POST['idk'];
        $pengirim = $_POST['pengirim'];
        $qty = $_POST['qty'];
    
        // Ambil detail stok saat ini
        $lihatstok = mysqli_query($conn,"SELECT * FROM stock WHERE id_barang='$idb'");
        $stoknya = mysqli_fetch_array($lihatstok);
        if (isset($stoknya['stok'])) {
            $stokskrng = $stoknya['stok'];
        } else {
            echo "Gagal: Kolom stok tidak ditemukan.";
            exit;
        }
    
        // Ambil qty barang masuk saat ini
        $qtyskrng_query = mysqli_query($conn, "SELECT qty FROM keluar WHERE idkeluar='$idk'");
        $qtynya = mysqli_fetch_array($qtyskrng_query);
        if (isset($qtynya['qty'])) {
            $qtyskrng = $qtynya['qty'];
        } else {
            echo "Gagal: Kolom qty tidak ditemukan.";
            exit;
        }
    
        if($qty > $qtyskrng) {
            $selisih = $qty - $qtyskrng;   // Selisih antara qty baru dan lama
            $kurangin = $stokskrng + $selisih; // Perbarui stok sesuai dengan selisih
    
            // Perbarui tabel stok dan tabel masuk
            $kuranginstoknya = mysqli_query($conn, "UPDATE stock SET stok='$kurangin' WHERE id_barang='$idb'");
            $updatenya = mysqli_query($conn, "UPDATE keluar SET qty='$qty', no_po='$no_po' , pengirim='$pengirim' WHERE idkeluar='$idk'");
    
            if($kuranginstoknya && $updatenya) {
                header('location:masuk.php');
            } else {
                echo 'Gagal: ' . mysqli_error($conn);
            }
        } else {
            $selisih = $qtyskrng-$qty;
            $kurangin = $stokskrng + $selisih;
    
            $kuranginstoknya = mysqli_query($conn, "UPDATE stock SET stok='$kurangin' WHERE id_barang='$idb'");
            $updatenya = mysqli_query($conn, "UPDATE keluar SET qty='$qty', no_po='$no_po', pengirim='$pengirim' WHERE idkeluar='$idk'");
    
            if ($kuranginstoknya&&$updatenya) {
                header('location:keluar.php');
            } else {
                echo 'Gagal: ' . mysqli_error($conn);
            }
        }
    }
    
    // hapus barang keluar

    if(isset($_POST['hapusbarangkeluar'])){
        $idb = $_POST['idb'];
        $idk = $_POST['idk'];
        $pengirim = $_POST['pengirim'];
        $qty = $_POST['kty'];
        
        
        $getdatastok = mysqli_query($conn,"select * from stock where id_barang='$idb'");
        $data = mysqli_fetch_array($getdatastok);
        $stok = $data['stok'];

        $selisih = $stok + $qty;

        $update = mysqli_query($conn,"update stock set stok='$selisih' where id_barang='$idb'");
        $hapusdata = mysqli_query($conn,"delete from keluar where idkeluar='$idk'");

        if($update&&$hapusdata){
            header('location:keluar.php');

        }  else {
            header('location:keluar.php');
        }
    }

   ?>
