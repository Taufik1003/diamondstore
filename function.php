<?php
session_start();
 $conn = mysqli_connect("localhost","root","","stokbarang2");

//menambah barang
if(isset($_POST['addnewbarang'])){
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];
    $hargasatuan = $_POST['hargasatuan'];

    //gambar
    $allowed_extension = array('png', 'jpg');
    $nama = $_FILES['file']['name'];
    $dot = explode('.', $nama);
    $ekstensi = strtolower(end($dot));
    $ukuran = $_FILES['file']['size'];
    $file_tmp = $_FILES['file']['tmp_name'];

    //penamaan
    $image = md5(uniqid($nama,true) . time()).'.'.$ekstensi;

    //validasi udah ada atau belum
    $cek = mysqli_query($conn,"select * from stok where namabarang='$namabarang'");
    $hitung = mysqli_num_rows($cek);

    if($hitung<1){
        //jika belum ada

            //proses upload gambar
        if(in_array($ekstensi, $allowed_extension) === true){
            //validasi ukuran file
            if($ukuran < 15000000){
               move_uploaded_file($file_tmp, 'images/'.$image);

               $addtotable = mysqli_query($conn, "insert into stok (namabarang, deskripsi, stock, image, hargasatuan) values('$namabarang','$deskripsi','$stock', '$image', '$hargasatuan')");
                if($addtotable){
                    header ('location:index.php');
                }else{
                    echo 'Gagal';
                    header ('location:index.php');
                }

            }else{
                //kalau filenya lebih dari 15mb
                echo '
                <script>
                    alert("Ukuran terlalu besar");
                    window.location.href="index.php";
                </script>
                ';
            }
        }else{
            //kalau file tidak png / jpg
            echo '
            <script>
                alert("File harus png / jpg");
                window.location.href="index.php";
            </script>
            ';
        }

    }else{
        //jika sudah ada
        echo '
        <script>
        alert("Nama barang sudah terdaftar");
        window.location.href="index.php";
        </script>
        ';
    }
}

//menambah barang masuk
if(isset($_POST['barangmasuk'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $cekstocksekarang = mysqli_query($conn, "select * from stok where idbarang ='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    $tambahkanstocksekarangandenganquantity = $stocksekarang+$qty;

    $addtomasuk = mysqli_query($conn, "insert into masuk (idbarang, keterangan, qty) values('$barangnya','$penerima','$qty')");
    $updatestokmasuk = mysqli_query($conn, "update stok set stock ='$tambahkanstocksekarangandenganquantity' where idbarang='$barangnya'");
    if($addtomasuk&&$updatestokmasuk){
        header ('location:masuk.php');
    }else{
        echo 'gagal';
        header ('location:masuk.php');
    }
}

//menambah barang keluar
if(isset($_POST['barangkeluar'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $cekstocksekarang = mysqli_query($conn, "select * from stok where idbarang ='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    $hargasatuan = $ambildatanya['hargasatuan']; // ambil harga satuan dari tabel stok
    $totalharga = $hargasatuan * $qty; // hitung total harga

    $tambahkanstocksekarangandenganquantity = $stocksekarang - $qty;

    $addtokeluar = mysqli_query($conn, "insert into keluar (idbarang, penerima, qty, totalharga) values('$barangnya','$penerima','$qty', '$totalharga')");
    $updatestokmasuk = mysqli_query($conn, "update stok set stock ='$tambahkanstocksekarangandenganquantity' where idbarang='$barangnya'");
    if($addtokeluar && $updatestokmasuk){
        header ('location:keluar.php');
    } else {
        echo 'gagal';
        header ('location:keluar.php');
    }
}

//update barang (index.php)
if(isset($_POST['update'])){
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    //gambar
    $allowed_extension = array('png', 'jpg');
    $nama = $_FILES['file']['name'];
    $dot = explode('.', $nama);
    $ekstensi = strtolower(end($dot));
    $ukuran = $_FILES['file']['size'];
    $file_tmp = $_FILES['file']['tmp_name'];

    //penamaan
    $image = md5(uniqid($nama,true) . time()).'.'.$ekstensi;

    if($ukuran==0){
       //jika tidak ingin upload
       $update = mysqli_query($conn, "update stok set namabarang='$namabarang', deskripsi='$deskripsi' where idbarang = '$idb'");
       if($update){
           header ('location:index.php');
       }else{
           echo 'gagal';
           header ('location:index.php');
       }
    }else{
        //jika upload
        move_uploaded_file($file_tmp, 'images/'.$image);
        $update = mysqli_query($conn, "update stok set namabarang='$namabarang', deskripsi='$deskripsi', image='$image' where idbarang = '$idb'");
        if($update){
            header ('location:index.php');
        }else{
            echo 'gagal';
            header ('location:index.php');
        }
    }
   
}

//hapus barang (index.php)
if(isset($_POST['hapus'])){
    $idb = $_POST['idb'];

    $gambar = mysqli_query($conn, "select * from stok where idbarang='$idb'");
    $get = mysqli_fetch_array($gambar);
    $img = 'images/'.$get['image'];
    unlink($img);

    $hapus = mysqli_query($conn, "delete from stok where idbarang='$idb'");
    if($hapus){
        header ('location:index.php');
    }else{
        echo 'gagal';
        header ('location:index.php');
    }
}

//mengubah barang masuk (masuk.php)
if(isset($_POST['updatemasuk'])){
    $idb = $_POST['idb'];
    $idm = $_POST['idm'];
    $deskripsi = $_POST['keterangan'];
    $qty = $_POST['qty'];

    $lihatstock = mysqli_query($conn, "select * from stok where idbarang='$idb'");
    $stoknya = mysqli_fetch_array($lihatstock);
    $stockskrg = $stoknya['stock'];   

    $qtyskrg = mysqli_query($conn, "select * from masuk where idmasuk='$idm'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty>$qtyskrg){
        $selisih = $qty-$qtyskrg;
        $kurangin = $stockskrg + $selisih;
        $kurangistocknya = mysqli_query($conn, "update stok set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update masuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
        if($kurangistocknya&&$updatenya){
            header ('location:masuk.php');
            }else{
                echo 'gagal';
                header ('location:masuk.php');
        }
    }else{
        $selisih = $qtyskrg-$qty;
        $kurangin = $stockskrg - $selisih;
        $kurangistocknya = mysqli_query($conn, "update stok set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update masuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
        if($kurangistocknya&&$updatenya){
            header ('location:masuk.php');
            }else{
                echo 'gagal';
                header ('location:masuk.php');
        }
    }
}

//menghapus barang masuk
if(isset($_POST['hapusmasuk'])){
    $idb = $_POST['idb'];
    $qty = $_POST['kty'];
    $idm = $_POST['idm'];

    $getdatastock = mysqli_query($conn, "select * from stok where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stok = $data['stock'];

    $selisih = $stok-$qty;

    $update = mysqli_query($conn, "update stok set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from masuk where idmasuk='$idm'");
    if($update&&$hapusdata){
        header ('location:masuk.php');
        }else{
            header ('location:masuk.php');
    }
}

//mengubah data keluar
if(isset($_POST['updatekeluar'])){
    $idb = $_POST['idb'];
    $idk = $_POST['idk'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $lihatstock = mysqli_query($conn, "select * from stok where idbarang='$idb'");
    $stoknya = mysqli_fetch_array($lihatstock);
    $stockskrg = $stoknya['stock'];   

    $qtyskrg = mysqli_query($conn, "select * from keluar where idkeluar='$idk'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty>$qtyskrg){
        $selisih = $qty-$qtyskrg;
        $kurangin = $stockskrg - $selisih;
        $kurangistocknya = mysqli_query($conn, "update stok set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
        if($kurangistocknya&&$updatenya){
            header ('location:keluar.php');
            }else{
                echo 'gagal';
                header ('location:keluar.php');
        }
    }else{
        $selisih = $qtyskrg-$qty;
        $kurangin = $stockskrg + $selisih;
        $kurangistocknya = mysqli_query($conn, "update stok set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
        if($kurangistocknya&&$updatenya){
            header ('location:keluar.php');
            }else{
                echo 'gagal';
                header ('location:keluar.php');
        }
    }
}

//menghapus barang keluar
if(isset($_POST['hapuskeluar'])){
    $idb = $_POST['idb'];
    $qty = $_POST['kty'];
    $idk = $_POST['idk'];

    $getdatastock = mysqli_query($conn, "select * from stok where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stok = $data['stock'];

    $selisih = $stok+$qty;

    $update = mysqli_query($conn, "update stok set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from keluar where idkeluar='$idk'");
    if($update&&$hapusdata){
        header ('location:keluar.php');
        }else{
            header ('location:keluar.php');
    }
}
?>