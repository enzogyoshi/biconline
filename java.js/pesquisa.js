const inputBusca = document.getElementById('input-busca');
const dropdown = document.getElementById('resultados-busca');
let timeoutBusca = null;

if (inputBusca && dropdown) {
    inputBusca.addEventListener('input', () => {
        clearTimeout(timeoutBusca);
        const termo = inputBusca.value.trim();

        if (termo.length < 2) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            return;
        }

        timeoutBusca = setTimeout(() => {
            buscarUsuarios(termo);
        }, 300);
    });
}

async function buscarUsuarios(termo) {
    try {
        const response = await fetch(`buscar_usuarios.php?q=${encodeURIComponent(termo)}`);
        const textoBruto = await response.text(); // Lê como texto primeiro para diagnosticar erros PHP

        let usuarios;
        try {
            usuarios = JSON.parse(textoBruto);
        } catch (e) {
            console.error('O PHP retornou HTML/Erro em vez de JSON:', textoBruto);
            return;
        }

        renderizarResultados(usuarios);
    } catch (erro) {
        console.error('Erro na requisição de busca:', erro);
    }
}

function renderizarResultados(usuarios) {
    dropdown.innerHTML = '';

    if (!Array.isArray(usuarios) || usuarios.length === 0) {
        dropdown.innerHTML = '<div style="padding: 12px; color: #777; font-size: 14px;">Nenhum usuário encontrado.</div>';
        dropdown.style.display = 'block';
        return;
    }

    usuarios.forEach(user => {
        const foto = user.foto ? user.foto : 'assets/img/avatar-padrao.png';
        
        const link = document.createElement('a');
        link.href = `/biconline/php/perfil.php?pesquisa=${user.codigo_url}`;
        link.className = 'search-item';
        
        link.innerHTML = `
            <img src="${foto}" alt="${user.nome}">
            <span>${user.nome}</span>
        `;
        
        dropdown.appendChild(link);
    });

    dropdown.style.display = 'block';
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-container')) {
        if (dropdown) dropdown.style.display = 'none';
    }
});