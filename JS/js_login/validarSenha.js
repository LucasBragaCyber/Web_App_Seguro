document.addEventListener('DOMContentLoaded', function () {
	const senhaInput = document.getElementById('senha');
	const confirmarSenha = document.getElementById('confirmarSenha');
	const mensagemSenha = document.getElementById('mensagem-senha');
	const mensagemConfirmar = document.getElementById('mensagem-confirmar');
	const toggleSenha = document.getElementById('toggleSenha');
	const toggleConfirmar = document.getElementById('toggleConfirmar');

	// Verifica força da senha
	function verificarForcaSenha() {
		const senha = senhaInput.value;
		let forca = 0;

		if (senha.length >= 8) forca++;
		if (/[A-Z]/.test(senha)) forca++;
		if (/[0-9]/.test(senha)) forca++;
		if (/[^A-Za-z0-9]/.test(senha)) forca++;

		if (senha.length === 0) {
			mensagemSenha.innerText = '';
		} else if (forca < 3) {
			mensagemSenha.innerText = 'Senha fraca';
			mensagemSenha.style.color = '#ffc107';
		} else if (forca === 3) {
			mensagemSenha.innerText = 'Senha média';
			mensagemSenha.style.color = '#17a2b8';
		} else {
			mensagemSenha.innerText = 'Senha forte';
			mensagemSenha.style.color = '#28a745';
		}
	}

	// Verifica se as senhas coincidem
	function verificarConfirmacaoSenha() {
		if (!senhaInput || !confirmarSenha || !mensagemConfirmar) return;

		if (confirmarSenha.value === '') {
			mensagemConfirmar.innerText = '';
		} else if (confirmarSenha.value !== senhaInput.value) {
			mensagemConfirmar.innerText = 'As senhas não coincidem';
			mensagemConfirmar.style.color = '#ffc107';
		} else {
			mensagemConfirmar.innerText = 'As senhas coincidem';
			mensagemConfirmar.style.color = '#28a745';
		}
	}

	// Alternar visibilidade da senha
	if (toggleSenha) {
		toggleSenha.addEventListener('click', function () {
			const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
			senhaInput.setAttribute('type', type);
			this.classList.toggle('bi-eye-slash-fill');
		});
	}

	if (toggleConfirmar) {
		toggleConfirmar.addEventListener('click', function () {
			const type = confirmarSenha.getAttribute('type') === 'password' ? 'text' : 'password';
			confirmarSenha.setAttribute('type', type);
			this.classList.toggle('bi-eye-slash-fill');
		});
	}

	// Eventos para atualizar dinamicamente
	if (senhaInput) {
		senhaInput.addEventListener('input', function () {
			verificarForcaSenha();
			verificarConfirmacaoSenha();
		});
	}

	if (confirmarSenha) {
		confirmarSenha.addEventListener('input', verificarConfirmacaoSenha);
	}
});
