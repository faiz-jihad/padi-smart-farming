from app.infrastructure.machine_learning.label_mapper import LabelMapper


def test_label_mapper_maps_configured_index_to_app_code():
    mapper = LabelMapper({"0": "healthy", "1": "blast"})

    disease_code, disease_name = mapper.map_index(1)

    assert disease_code == "blast"
    assert disease_name == "Blast (Penyakit Blas)"


def test_label_mapper_falls_back_to_unknown_for_missing_index():
    mapper = LabelMapper({"0": "healthy"})

    disease_code, disease_name = mapper.map_index(5)

    assert disease_code == "unknown"
    assert disease_name == "Tidak Dapat Dipastikan"


def test_label_mapper_normalizes_human_readable_class_labels():
    mapper = LabelMapper({"0": "Normal (Padi Sehat)", "1": "Blast (Penyakit Blas)"})

    disease_code, disease_name = mapper.map_index(0)

    assert disease_code == "healthy"
    assert disease_name == "Normal (Padi Sehat)"
