// Gestionnaire de popup pour la sélection des catégories
class CategoriesPopupManager {
    constructor() {
        this.popup = null;
        this.selectorBtn = null;
        this.selectedCategories = new Set();
        this.symfonySelect = null;
        this.init();
    }

    init() {
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.popup = document.getElementById('categories-popup-overlay');
        this.selectorBtn = document.getElementById('categories-selector-btn');
        this.symfonySelect = document.querySelector('select[multiple]');
        
        if (!this.popup || !this.selectorBtn) return;

        this.bindEvents();
        this.loadPreselectedCategories();
    }

    bindEvents() {
        const closeBtn = document.getElementById('categories-popup-close');
        const cancelBtn = document.getElementById('categories-cancel');
        const confirmBtn = document.getElementById('categories-confirm');
        const selectedDisplay = document.getElementById('categories-selected-display');

        // Ouvrir la popup
        this.selectorBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.openPopup();
        });

        // Fermer la popup
        closeBtn?.addEventListener('click', () => this.closePopup());
        cancelBtn?.addEventListener('click', () => this.closePopup());

        // Fermer en cliquant sur l'overlay
        this.popup.addEventListener('click', (e) => {
            if (e.target === this.popup) {
                this.closePopup();
            }
        });

        // Gestion des groupes repliables
        document.querySelectorAll('.categories-group-header').forEach(header => {
            header.addEventListener('click', () => this.toggleGroup(header));
        });

        // Gestion des cases à cocher
        document.querySelectorAll('.categories-popup input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.handleCheckboxChange(checkbox));
        });

        // Confirmer la sélection
        confirmBtn?.addEventListener('click', () => this.confirmSelection());

        // Supprimer une catégorie depuis l'affichage
        selectedDisplay?.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove')) {
                this.removeCategory(e.target.dataset.category);
            }
        });

        // Fermer avec Echap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.popup.classList.contains('active')) {
                this.closePopup();
            }
        });
    }

    loadPreselectedCategories() {
        if (this.symfonySelect) {
            Array.from(this.symfonySelect.selectedOptions).forEach(option => {
                this.selectedCategories.add(option.text);
                const checkbox = document.querySelector(`input[data-category="${option.text}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
            this.updateDisplay();
        }
    }

    openPopup() {
        this.popup.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    closePopup() {
        this.popup.classList.remove('active');
        document.body.style.overflow = '';
    }

    toggleGroup(header) {
        const groupContent = header.nextElementSibling;
        const isCollapsed = header.classList.contains('collapsed');
        
        if (isCollapsed) {
            header.classList.remove('collapsed');
            groupContent.classList.remove('collapsed');
        } else {
            header.classList.add('collapsed');
            groupContent.classList.add('collapsed');
        }
    }

    handleCheckboxChange(checkbox) {
        const category = checkbox.dataset.category;
        
        if (checkbox.checked) {
            this.selectedCategories.add(category);
        } else {
            this.selectedCategories.delete(category);
        }
        
        this.updateSelectedCount();
    }

    removeCategory(category) {
        this.selectedCategories.delete(category);
        
        // Décocher la case correspondante
        const checkbox = document.querySelector(`input[data-category="${category}"]`);
        if (checkbox) {
            checkbox.checked = false;
        }
        
        this.updateDisplay();
        this.updateSymfonySelect();
    }

    confirmSelection() {
        this.updateSymfonySelect();
        this.updateDisplay();
        this.closePopup();
    }

    updateSelectedCount() {
        const count = this.selectedCategories.size;
        const selectedCount = document.getElementById('selected-count');
        if (selectedCount) {
            selectedCount.textContent = `${count} catégorie(s) sélectionnée(s)`;
        }
    }

    updateSymfonySelect() {
        if (!this.symfonySelect) return;
        
        // Désélectionner toutes les options
        Array.from(this.symfonySelect.options).forEach(option => {
            option.selected = this.selectedCategories.has(option.text);
        });
    }

    updateDisplay() {
        const placeholder = document.getElementById('categories-placeholder');
        const selectedDisplay = document.getElementById('categories-selected-display');
        
        if (!placeholder || !selectedDisplay) return;

        if (this.selectedCategories.size === 0) {
            placeholder.textContent = 'Choisir les catégories...';
            selectedDisplay.innerHTML = '';
        } else {
            placeholder.textContent = `${this.selectedCategories.size} catégorie(s) sélectionnée(s)`;
            
            selectedDisplay.innerHTML = '';
            this.selectedCategories.forEach(category => {
                const tag = document.createElement('div');
                tag.className = 'category-tag';
                tag.innerHTML = `
                    ${category}
                    <span class="remove" data-category="${category}">&times;</span>
                `;
                selectedDisplay.appendChild(tag);
            });
        }
        this.updateSelectedCount();
    }
}

// Créer une instance globale
window.categoriesPopupManager = new CategoriesPopupManager();
