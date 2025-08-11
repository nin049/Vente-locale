// Gestionnaire de popup pour la sélection d'une seule catégorie
class CategorySelectorManager {
    constructor() {
        this.popup = null;
        this.selectorBtn = null;
        this.selectedCategory = null;
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
        this.symfonySelect = document.querySelector('select:not([multiple])');
        
        if (!this.popup || !this.selectorBtn) return;

        this.bindEvents();
        this.loadPreselectedCategory();
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

        // Gestion des cases à cocher (radio behavior)
        document.querySelectorAll('.categories-popup input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.handleCheckboxChange(checkbox));
        });

        // Confirmer la sélection
        confirmBtn?.addEventListener('click', () => this.confirmSelection());

        // Supprimer la catégorie depuis l'affichage
        selectedDisplay?.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove')) {
                this.removeCategory();
            }
        });

        // Fermer avec Echap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.popup.classList.contains('active')) {
                this.closePopup();
            }
        });
    }

    loadPreselectedCategory() {
        if (this.symfonySelect && this.symfonySelect.value) {
            const selectedOption = this.symfonySelect.selectedOptions[0];
            if (selectedOption) {
                this.selectedCategory = selectedOption.text;
                const checkbox = document.querySelector(`input[data-category="${selectedOption.text}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
                this.updateDisplay();
            }
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
            // Décocher toutes les autres cases (comportement radio)
            document.querySelectorAll('.categories-popup input[type="checkbox"]').forEach(cb => {
                if (cb !== checkbox) {
                    cb.checked = false;
                }
            });
            this.selectedCategory = category;
        } else {
            this.selectedCategory = null;
        }
        
        this.updateSelectedCount();
    }

    removeCategory() {
        this.selectedCategory = null;
        
        // Décocher toutes les cases
        document.querySelectorAll('.categories-popup input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        
        this.updateDisplay();
        this.updateSymfonySelect();
    }

    confirmSelection() {
        this.updateSymfonySelect();
        this.updateDisplay();
        this.closePopup();
    }

    updateSelectedCount() {
        const count = this.selectedCategory ? 1 : 0;
        const selectedCount = document.getElementById('selected-count');
        if (selectedCount) {
            selectedCount.textContent = count > 0 ? `1 catégorie sélectionnée` : `Aucune catégorie sélectionnée`;
        }
    }

    updateSymfonySelect() {
        if (!this.symfonySelect) return;
        
        // Trouver l'option correspondante et la sélectionner
        Array.from(this.symfonySelect.options).forEach(option => {
            option.selected = (option.text === this.selectedCategory);
        });
    }

    updateDisplay() {
        const placeholder = document.getElementById('categories-placeholder');
        const selectedDisplay = document.getElementById('categories-selected-display');
        
        if (!placeholder || !selectedDisplay) return;

        if (!this.selectedCategory) {
            placeholder.textContent = 'Choisir une catégorie...';
            selectedDisplay.innerHTML = '';
        } else {
            placeholder.textContent = this.selectedCategory;
            
            selectedDisplay.innerHTML = `
                <div class="category-tag">
                    ${this.selectedCategory}
                    <span class="remove">&times;</span>
                </div>
            `;
        }
        this.updateSelectedCount();
    }
}

// Créer une instance globale
window.categorySelectorManager = new CategorySelectorManager();
