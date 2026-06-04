from icrawler.builtin import BingImageCrawler

keywords = [
    "agricultural drone",
    "DJI Agras",
    "spraying drone",
    "crop spraying UAV",
    "industrial drone"
]

for keyword in keywords:
    crawler = BingImageCrawler(
        storage={'root_dir': 'public/uploads/products'}
    )

    crawler.crawl(
        keyword=keyword,
        max_num=40
    )