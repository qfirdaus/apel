// Contoh untuk submit form Work Experience
document.getElementById('formWorkExp').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let formData = new FormData(this);
    formData.append('action', 'add_work_exp'); // Flag penentu untuk Controller

    fetch(base_url + 'pages/portfolio/informal/ajax_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if(data.status === 'success') {
            $('#modalAddWork').modal('hide');
            location.reload(); // atau update table via ajax
        }
    });
});