const menuColors = {
    home: '#FFFFFF',
    task: '#A7FEB3',
    mining: '#FF8484',
    friends: '#FFFCAB',
    wallet: '#9B9BFF'
};

let currentActiveItem = null;

function activateMenuItem(item) {
    const img = item.querySelector('img');
    const text = item.dataset.text;

    if (img) {
        img.style.display = 'none'; // Nascondi completamente l'icona
    }

    if (!item.querySelector('span')) {
        const span = document.createElement('span');
        span.textContent = text;
        item.appendChild(span);
    }

    const span = item.querySelector('span');
    if (span) {
        span.style.display = 'block'; // Mostra il testo
        span.style.color = menuColors[item.dataset.page];
    }

    item.classList.add('active');
    currentActiveItem = item;
}


function resetMenuItems(excludeItem = null) {
    document.querySelectorAll('.menu-item').forEach(item => {
        if (item === excludeItem) return;

        const img = item.querySelector('img');
        if (img) {
            img.style.display = 'block'; // Mostra l'icona
        }

        const span = item.querySelector('span');
        if (span) {
            span.style.display = 'none'; // Nascondi il testo
        }

        item.classList.remove('active'); // Rimuovi la classe active
    });
}



function getCurrentPage() {
    const page = window.location.pathname.split('/').pop().replace('.php', '');
    return page === 'homepage' ? 'home' : page;
}

document.addEventListener('DOMContentLoaded', () => {
    const currentPage = getCurrentPage();
    const currentMenuItem = document.querySelector(`.menu-item[data-page="${currentPage}"]`);
    if (currentMenuItem) {
        resetMenuItems(currentMenuItem);
        activateMenuItem(currentMenuItem);
    }
});





document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('mouseover', function () {
        resetMenuItems(currentActiveItem);
        activateMenuItem(this);
       
    });

    item.addEventListener('mouseout', function () {
        resetMenuItems(currentActiveItem);
        if (currentActiveItem) {
            activateMenuItem(currentActiveItem);
        }
    });

    item.addEventListener('click', function (event) {
        event.preventDefault(); // Previeni il comportamento predefinito del link
    
        if (this.id !== "walletButton") {
            $("#wallet").css("display", "none");
        } else {
            $("#wallet").css("display", "flex");
        }
    
        const targetSectionId = this.dataset.page;
        const targetSection = document.getElementById(targetSectionId);
    
        // Reset del menu e attivazione della voce cliccata
        resetMenuItems(this);
        activateMenuItem(this);
    
        // Gestione delle sezioni
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });
    
        if (targetSection) {
            targetSection.classList.add('active');
            targetSection.scrollIntoView({ behavior: 'instant', block: 'start' });
        }
    });
    
    
    
});



