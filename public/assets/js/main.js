window.addEventListener('load', function() {
    let phoneInput = document.getElementById('phone-input');
    let name = document.getElementById('name-input');
    let from = document.getElementById('from-input');
    let comment = document.getElementById('comment-input');
    let clientId = document.getElementById('client-id');

    if(phoneInput) {
        phoneInput.addEventListener('change', (ev) => {
            let phone = ev.target.value.replace(/\D/g, '');

            if(phone.length === 11) {

                axios.post('/clients/check', {
                    phone: phone,
                })
                    .then(function (response) {
                        let data = response.data;

                        name.value = data.name;
                        from.value = data.from;
                        comment.value = data.comment;
                        clientId.value = data.id;

                        name.closest('.form-group').classList.remove('hidden');
                        from.closest('.form-group').classList.add('hidden');
                        comment.closest('.form-group').classList.add('hidden');
                    })
                    .catch(function (error) {
                        name.value = null;
                        // from.value = null;
                        comment.value = null;
                        clientId.value = null;

                        name.closest('.form-group').classList.remove('hidden');
                        from.closest('.form-group').classList.remove('hidden');
                        comment.closest('.form-group').classList.remove('hidden');
                    })
                    .finally(function () {
                        // always executed
                    });
            }
        });
    }
});
