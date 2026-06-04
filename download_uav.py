from bing_image_downloader import downloader

keywords = [
    "agricultural drone",
    "DJI Agras",
    "spraying drone",
    "crop spraying UAV",
    "industrial drone",
    "mapping drone",
    "survey drone"
]

for keyword in keywords:
    print("Downloading:", keyword)

    downloader.download(
        keyword,
        limit=50,
        output_dir="public/uploads/products",
        adult_filter_off=True,
        force_replace=False,
        timeout=60
    )

print("DONE")