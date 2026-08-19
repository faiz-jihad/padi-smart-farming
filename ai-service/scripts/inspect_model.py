from __future__ import annotations

import argparse
import json
from pathlib import Path

import h5py


def inspect_model(model_path: Path) -> dict:
    with h5py.File(model_path, "r") as model_file:
        model_config = json.loads(model_file.attrs["model_config"])
    layers = model_config["config"]["layers"]
    return {
        "format": "keras_h5",
        "class_name": model_config["class_name"],
        "build_input_shape": model_config["config"].get("build_input_shape"),
        "last_layer": layers[-1]["class_name"],
        "last_layer_units": layers[-1]["config"].get("units"),
        "last_layer_activation": layers[-1]["config"].get("activation"),
    }


def main() -> None:
    parser = argparse.ArgumentParser(description="Inspect Keras H5 model metadata.")
    parser.add_argument("model_path", type=Path)
    args = parser.parse_args()
    print(json.dumps(inspect_model(args.model_path), indent=2))


if __name__ == "__main__":
    main()
