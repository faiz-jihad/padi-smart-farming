document.addEventListener('DOMContentLoaded', function () {
    const farmerSelect = document.getElementById('farmer_id');
    const farmSelect = document.getElementById('farm_id');
    const dataElement = document.getElementById('marketplace-form-data');

    if (!farmerSelect || !farmSelect || !dataElement) {
        return;
    }

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

    farmerSelect.addEventListener('change', updateFarmOptions);

    updateFarmOptions();
});