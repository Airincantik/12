// Fungsi untuk mengambil lokasi peserta dan mengirimkan ke server
function sendLocation(participantId) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                // Kirim data lokasi ke server
                fetch('location.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `latitude=${position.coords.latitude}&longitude=${position.coords.longitude}&participant_id=${participantId}`,
                })
                    .then((response) => response.json())
                    .then((data) => {
                        console.log(data);
                        alert(data.message);
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    });
            },
            function (error) {
                alert('Error retrieving location: ' + error.message);
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}
