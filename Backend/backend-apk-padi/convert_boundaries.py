import glob
import re
import json
import os

files = glob.glob("temp-boundaries/db/kec/*.sql")

features = []

pattern = re.compile(
    r"\('([^']+)','([^']+)',"
    r"(-?\d+(?:\.\d+)?),"
    r"(-?\d+(?:\.\d+)?),"
    r"'(.*?)'\)"
)

print(f"SQL FILES: {len(files)}")

for index, file in enumerate(files, 1):
    print(f"[{index}/{len(files)}] {os.path.basename(file)}")

    with open(file, encoding="utf-8") as f:
        content = f.read()

    for match in pattern.finditer(content):
        code = match.group(1)
        name = match.group(2)
        path = match.group(5)

        points = re.findall(
            r"\[(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)\]",
            path
        )

        if len(points) < 3:
            continue

        # SQL = [lat, lng]
        # GeoJSON = [lng, lat]
        coordinates = [
            [float(lng), float(lat)]
            for lat, lng in points
        ]

        features.append({
            "type": "Feature",
            "properties": {
                "code": code,
                "name": name
            },
            "geometry": {
                "type": "Polygon",
                "coordinates": [coordinates]
            }
        })

output = {
    "type": "FeatureCollection",
    "features": features
}

os.makedirs("storage/app/data", exist_ok=True)

with open(
    "storage/app/data/district_boundaries.geojson",
    "w",
    encoding="utf-8"
) as f:
    json.dump(output, f, ensure_ascii=False)

print()
print("================================")
print(f"DONE")
print(f"POLYGONS : {len(features)}")
print(
    "SIZE     :",
    os.path.getsize("storage/app/data/district_boundaries.geojson"),
    "bytes"
)
print("================================")