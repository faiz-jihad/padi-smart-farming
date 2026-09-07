document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // FARMER & FARM
    // =========================================================

    const farmerSelect = document.getElementById('farmer_id');
    const farmSelect = document.getElementById('farm_id');
    const dataElement = document.getElementById('marketplace-form-data');

    if (farmerSelect && farmSelect && dataElement) {
        const farms = JSON.parse(dataElement.dataset.farms);

        function updateFarmOptions() {
            const farmerId = farmerSelect.value;

            farmSelect.innerHTML = '';

            if (!farmerId || !farms[farmerId]) {
                farmSelect.disabled = true;

                const option = document.createElement('option');

                option.value = '';
                option.textContent = '-- Pilih Petani Terlebih Dahulu --';

                farmSelect.appendChild(option);

                return;
            }

            const defaultOption = document.createElement('option');

            defaultOption.value = '';
            defaultOption.textContent = '-- Pilih Lahan --';

            farmSelect.appendChild(defaultOption);

            farms[farmerId].forEach(function (farm) {
                const option = document.createElement('option');

                option.value = farm.id;
                option.textContent = farm.name;

                farmSelect.appendChild(option);
            });

            farmSelect.disabled = false;
        }

        farmerSelect.addEventListener(
            'change',
            updateFarmOptions
        );

        updateFarmOptions();
    }


    // =========================================================
    // CATEGORY
    // =========================================================

    const categorySelect =
        document.getElementById('category_id');

    const modal =
        document.getElementById('add-category-modal');

    const openModalButton =
        document.getElementById('open-add-category-modal');

    const closeModalButton =
        document.getElementById('close-add-category-modal');

    const cancelModalButton =
        document.getElementById('cancel-add-category');

    const categoryForm =
        document.getElementById('add-category-form');

    const categoryNameInput =
        document.getElementById('new_category_name');

    const categorySlugInput =
        document.getElementById('new_category_slug');

    const categoryError =
        document.getElementById('add-category-error');

    const saveCategoryButton =
        document.getElementById('save-category-button');

    const categoryActiveInput =
        document.getElementById('new_category_is_active');


    if (
        !categorySelect ||
        !modal ||
        !openModalButton ||
        !closeModalButton ||
        !cancelModalButton ||
        !categoryForm
    ) {
        return;
    }


    // =========================================================
    // OPEN MODAL
    // =========================================================

    function openCategoryModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        document.body.style.overflow = 'hidden';

        hideCategoryError();

        setTimeout(function () {
            if (categoryNameInput) {
                categoryNameInput.focus();
            }
        }, 100);
    }


    // =========================================================
    // CLOSE MODAL
    // =========================================================

    function closeCategoryModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        document.body.style.overflow = '';

        hideCategoryError();
    }


    function hideCategoryError() {
        if (!categoryError) {
            return;
        }

        categoryError.textContent = '';
        categoryError.classList.remove('is-visible');
    }


    function showCategoryError(message) {
        if (!categoryError) {
            return;
        }

        categoryError.textContent = message;
        categoryError.classList.add('is-visible');
    }


    openModalButton.addEventListener(
        'click',
        openCategoryModal
    );

    closeModalButton.addEventListener(
        'click',
        closeCategoryModal
    );

    cancelModalButton.addEventListener(
        'click',
        closeCategoryModal
    );


    // Klik area luar modal
    modal.addEventListener('click', function (event) {

        if (event.target === modal) {
            closeCategoryModal();
        }

    });


    // Escape
    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            modal.classList.contains('is-open')
        ) {
            closeCategoryModal();
        }

    });


    // =========================================================
    // AUTO SLUG
    // =========================================================

    if (categoryNameInput && categorySlugInput) {

        categoryNameInput.addEventListener(
            'input',
            function () {

                if (
                    categorySlugInput.dataset.manual === 'true'
                ) {
                    return;
                }

                const slug = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');

                categorySlugInput.value = slug;
            }
        );


        categorySlugInput.addEventListener(
            'input',
            function () {
                this.dataset.manual = 'true';
            }
        );

    }


    // =========================================================
    // SUBMIT CATEGORY
    // =========================================================

    categoryForm.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();

            hideCategoryError();

            saveCategoryButton.disabled = true;
            saveCategoryButton.textContent = 'Menyimpan...';


            try {

                const formData =
                    new FormData(categoryForm);


                const response = await fetch(
                    categoryForm.action,
                    {
                        method: 'POST',

                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },

                        body: formData
                    }
                );


                const result =
                    await response.json();


                if (
                    !response.ok ||
                    !result.success
                ) {

                    let message =
                        result.message ||
                        'Kategori gagal ditambahkan.';


                    if (result.errors) {

                        const validationMessages =
                            Object.values(result.errors)
                                .flat();


                        if (
                            validationMessages.length > 0
                        ) {
                            message =
                                validationMessages.join(' ');
                        }

                    }

                    throw new Error(message);
                }


                const category =
                    result.data;


                // Tambahkan kategori ke dropdown
                const option =
                    document.createElement('option');

                option.value =
                    category.id;

                option.textContent =
                    category.name;

                option.dataset.slug =
                    category.slug || '';

                option.dataset.icon =
                    category.icon || '';


                categorySelect.appendChild(option);


                // Pilih otomatis
                categorySelect.value =
                    category.id;


                // Reset modal
                categoryForm.reset();


                if (categoryActiveInput) {
                    categoryActiveInput.checked = true;
                }


                if (categorySlugInput) {
                    categorySlugInput.dataset.manual =
                        'false';
                }


                closeCategoryModal();


                // Toast
                showCategoryNotification(
                    'Kategori "' +
                    category.name +
                    '" berhasil ditambahkan.'
                );

            } catch (error) {

                showCategoryError(
                    error.message ||
                    'Terjadi kesalahan saat menambahkan kategori.'
                );

            } finally {

                saveCategoryButton.disabled =
                    false;

                saveCategoryButton.textContent =
                    'Simpan Kategori';

            }

        }
    );


    // =========================================================
    // TOAST NOTIFICATION
    // =========================================================

    function showCategoryNotification(message) {

        const existing =
            document.getElementById(
                'category-notification'
            );


        if (existing) {
            existing.remove();
        }


        const notification =
            document.createElement('div');

        notification.id =
            'category-notification';

        notification.className =
            'category-notification';


        const icon =
            document.createElement('span');

        icon.className =
            'category-notification-icon';

        icon.textContent =
            '✓';


        const text =
            document.createElement('span');

        text.textContent =
            message;


        notification.appendChild(icon);
        notification.appendChild(text);

        document.body.appendChild(notification);


        requestAnimationFrame(function () {
            notification.classList.add('show');
        });


        setTimeout(function () {

            notification.classList.remove('show');

            setTimeout(function () {
                notification.remove();
            }, 250);

        }, 3000);

    }

});
