document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modalTitle');
    const userForm = document.getElementById('userForm');
    
    // Éléments du formulaire
    const userId = document.getElementById('userId');
    const userName = document.getElementById('userName');
    const userEmail = document.getElementById('userEmail');

    function openModal(mode, data = {}) {
        modal.classList.add('active'); // Utilisation d'une classe pour l'animation
        if (mode === 'create') {
            modalTitle.textContent = 'Nouvel Utilisateur';
            userForm.reset();
            userId.value = '';
        } else {
            modalTitle.textContent = 'Modifier l\'Utilisateur';
            userId.value = data.id || '';
            userName.value = data.name || '';
            userEmail.value = data.email || '';
        }
    }

    function closeModal() {
        modal.classList.remove('active');
    }

    // Fermer la modal si on clique à l'extérieur
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.getElementById('btnCreate').addEventListener('click', () => openModal('create'));
    document.getElementById('btnCancel').addEventListener('click', closeModal);

    document.getElementById('usersTable').addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btnEdit');
        const btnDelete = e.target.closest('.btnDelete');

        if (btnEdit) {
            const tr = btnEdit.closest('tr');
            const id = btnEdit.getAttribute('data-id');
            const name = tr.querySelector('.name').textContent.trim();
            const email = tr.querySelector('.email').textContent.trim();
            openModal('edit', {id, name, email});
        }

        if (btnDelete) {
            const id = btnDelete.getAttribute('data-id');
            if (confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) {
                // Animation optionnelle : griser la ligne pendant la suppression
                btnDelete.closest('tr').style.opacity = '0.5';
                
                fetch('/?action=delete', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + encodeURIComponent(id)
                }).then(r => r.json()).then(json => {
                    if (json.success) location.reload();
                    else alert('Erreur suppression');
                });
            }
        }
    });

    userForm.addEventListener('submit', function (ev) {
        ev.preventDefault();
        const action = userId.value ? 'update' : 'store';
        const formData = new FormData(userForm);

        // Désactiver le bouton pour éviter les doubles clics
        const submitBtn = userForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Chargement...';

        fetch('/?action=' + action, {
            method: 'POST',
            body: formData
        }).then(r => r.json()).then(json => {
            if (json.success) {
                closeModal();
                location.reload();
            } else {
                alert('Erreur lors de l\'enregistrement');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enregistrer';
            }
        }).catch(() => {
            alert('Erreur réseau');
            submitBtn.disabled = false;
        });
    });
});