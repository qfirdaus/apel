<?php
declare(strict_types=1);

class RegisterPortfolio
{
    private PDO $pdoSPK;

    public function __construct(?PDO $pdoSPK = null)
    {
        // Dalam contoh sebenar, connection database dimasukkan di sini
        $this->pdoSPK = $pdoSPK ?? new PDO('sqlite::memory:'); 
    }

    public function getSenaraiProgramDummy(): array
    {
        // Data dummy senarai program portfolio
        return [
            ['id' => 'PRG001', 'nama' => 'Portfolio Pengurusan Perniagaan', 'yuran' => 150.00],
            ['id' => 'PRG002', 'nama' => 'Portfolio Teknologi Maklumat', 'yuran' => 200.00],
            ['id' => 'PRG003', 'nama' => 'Portfolio Kejuruteraan Elektrik', 'yuran' => 250.00],
        ];
    }

    public function simpanPendaftaran(array $data, string $userID): bool
    {
        // Dummy logic untuk simpan data pendaftaran ke DB
        // INSERT INTO tbl_portfolio_register ...
        return true; 
    }
}
