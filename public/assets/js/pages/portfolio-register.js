document.addEventListener("DOMContentLoaded", function () {
    // 1. Initialise Select2
    if ($('.select2').length) {$('.select2').select2();
    }

    const selectProgram = document.getElementById('id_program');
    const paparanYuran = document.getElementById('paparan_yuran');
    const gwAmountInput = document.getElementById('gw_amount');
    const paymentMethods = document.querySelectorAll('.payment-method');
    const sectionManual = document.getElementById('section_manual');
    const btnHantar = document.getElementById('btnHantarPendaftaran');
    const formGateway = document.getElementById('formKewanganGateway');

    // 2. Kemaskini harga bila program ditukar
    $('#id_program').on('change', function() {
        let yuran = $(this).find(':selected').data('yuran');
        if (yuran) {
            paparanYuran.innerHTML = `RM ${parseFloat(yuran).toFixed(2)}`;
            gwAmountInput.value = yuran; // Set untuk hidden form gateway
        }
    });

    // 3. Togol paparan berdasarkan kaedah bayaran
    paymentMethods.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'manual') {
                sectionManual.style.display = 'block';
                btnHantar.innerHTML = '<i class="ri-upload-cloud-line me-1"></i> Hantar & Muat Naik Resit';
            } else {
                sectionManual.style.display = 'none';
                btnHantar.innerHTML = '<i class="ri-secure-payment-line me-1"></i> Bayar Melalui FPX';
            }
        });
    });

    // 4. Proses Submit Borang
    btnHantar.addEventListener('click', function () {
        // Semakan asas
        if (!selectProgram.value) {
            alert("Sila pilih program terlebih dahulu!");
            return;
        }

        const method = document.querySelector('input[name="kaedah_bayaran"]:checked').value;

        if (method === 'manual') {
            // CARA 1: Hantar data & fail guna AJAX
            const fileInput = document.getElementById('resit_bayaran');
            if (fileInput.files.length === 0) {
                alert("Sila muat naik resit bayaran anda.");
                return;
            }

            let formData = new FormData(document.getElementById('formDaftarPortfolio'));
            
            // Dummy ajax call (anda perlu buat endpoint AJAX .php yang memanggil RegisterController)
            btnHantar.disabled = true;
            btnHantar.innerHTML = "Sedang diproses...";

            fetch(base_url + 'pages/portfolio/register/ajax_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    window.location.href = base_url + 'pages/portfolio/status/index.php'; // redirect
                } else {
                    alert(data.message);
                    btnHantar.disabled = false;
                }
            })
            .catch(error => {
                console.error("Error:", error);
                btnHantar.disabled = false;
            });

        } else if (method === 'gateway') {
            // CARA 2: Push Data (POST) ke Sistem Kewangan
            // Kita generate Order ID dummy
            document.getElementById('gw_order_id').value = "PORTFOLIO-" + Date.now();
            
            // Hantar (Submit) form tersembunyi tersebut ke URL kewangan gateway
            formGateway.submit();
        }
    });
});