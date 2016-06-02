-- 
-- Disable foreign keys
-- 
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- 
-- Set character set the client will use to send SQL statements to the server
--
SET NAMES 'utf8';

-- 
-- Set default database
--
USE kuliah;

--
-- Definition for table app_user
--
DROP TABLE IF EXISTS app_user;
CREATE TABLE IF NOT EXISTS app_user (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  account_expired bit(1) NOT NULL,
  account_locked bit(1) NOT NULL,
  address varchar(150) DEFAULT NULL,
  city varchar(50) DEFAULT NULL,
  country varchar(100) DEFAULT NULL,
  postal_code varchar(15) DEFAULT NULL,
  province varchar(100) DEFAULT NULL,
  credentials_expired bit(1) NOT NULL,
  email varchar(255) NOT NULL,
  account_enabled bit(1) DEFAULT NULL,
  first_name varchar(50) NOT NULL,
  last_name varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  password_hint varchar(255) DEFAULT NULL,
  phone_number varchar(255) DEFAULT NULL,
  username varchar(50) NOT NULL,
  version int(11) DEFAULT NULL,
  website varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX uk_1j9d9a06i600gd43uu3km82jw (email),
  UNIQUE INDEX uk_3k4cplvh82srueuttfkwnylq0 (username)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Definition for table dosen
--
DROP TABLE IF EXISTS dosen;
CREATE TABLE IF NOT EXISTS dosen (
  nip int(20) NOT NULL,
  nama_dosen varchar(255) NOT NULL,
  jenis_kelamin varchar(10) NOT NULL,
  alamat varchar(255) NOT NULL,
  tempat_lahir varchar(50) NOT NULL,
  tgl_lahir date NOT NULL,
  profile_img varchar(255) DEFAULT NULL,
  PRIMARY KEY (nip)
)
ENGINE = INNODB
CHARACTER SET latin1
COLLATE latin1_swedish_ci;

--
-- Definition for table jurusan
--
DROP TABLE IF EXISTS jurusan;
CREATE TABLE IF NOT EXISTS jurusan (
  kode_jurusan varchar(15) NOT NULL,
  nama_jurusan varchar(255) DEFAULT NULL,
  PRIMARY KEY (kode_jurusan)
)
ENGINE = INNODB
CHARACTER SET latin1
COLLATE latin1_swedish_ci;

--
-- Definition for table role
--
DROP TABLE IF EXISTS role;
CREATE TABLE IF NOT EXISTS role (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  description varchar(64) DEFAULT NULL,
  name varchar(20) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Definition for table mahasiswa
--
DROP TABLE IF EXISTS mahasiswa;
CREATE TABLE IF NOT EXISTS mahasiswa (
  nim int(11) NOT NULL AUTO_INCREMENT,
  kode_jurusan varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  nama varchar(255) DEFAULT NULL,
  tempat_lahir varchar(255) DEFAULT NULL,
  tanggal_lahir date DEFAULT NULL,
  jenis_kelamin varchar(255) DEFAULT NULL,
  alamat varchar(255) DEFAULT NULL,
  PRIMARY KEY (nim),
  CONSTRAINT fk_mahasiswa_jurusan_kode_jurusan FOREIGN KEY (kode_jurusan)
  REFERENCES jurusan (kode_jurusan) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Definition for table matakuliah
--
DROP TABLE IF EXISTS matakuliah;
CREATE TABLE IF NOT EXISTS matakuliah (
  kode_mk varchar(20) NOT NULL,
  jurusan_kode varchar(20) NOT NULL,
  kd_dosen int(20) DEFAULT NULL,
  nama_matakuliah varchar(255) NOT NULL,
  jumlah_sks int(11) NOT NULL,
  PRIMARY KEY (kode_mk),
  CONSTRAINT fk_matakuliah_dosen_nip FOREIGN KEY (kd_dosen)
  REFERENCES dosen (nip) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_matakuliah_jurusan_kode_jurusan FOREIGN KEY (jurusan_kode)
  REFERENCES jurusan (kode_jurusan) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
CHARACTER SET latin1
COLLATE latin1_swedish_ci;

--
-- Definition for table user_role
--
DROP TABLE IF EXISTS user_role;
CREATE TABLE IF NOT EXISTS user_role (
  user_id bigint(20) NOT NULL,
  role_id bigint(20) NOT NULL,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_apcc8lxk2xnug8377fatvbn04 FOREIGN KEY (user_id)
  REFERENCES app_user (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_it77eq964jhfqtu54081ebtio FOREIGN KEY (role_id)
  REFERENCES role (id) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Definition for table nilai
--
DROP TABLE IF EXISTS nilai;
CREATE TABLE IF NOT EXISTS nilai (
  id int(11) NOT NULL AUTO_INCREMENT,
  nim int(11) NOT NULL,
  kode_matakuliah varchar(20) DEFAULT NULL,
  nilai int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_nilai_mahasiswa_nim FOREIGN KEY (nim)
  REFERENCES mahasiswa (nim) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_nilai_matakuliah_kode_mk FOREIGN KEY (kode_matakuliah)
  REFERENCES matakuliah (kode_mk) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET latin1
COLLATE latin1_swedish_ci;

-- 
-- Enable foreign keys
-- 
/*!40014 SET foreign_key_checks = @OLD_FOREIGN_KEY_CHECKS */;