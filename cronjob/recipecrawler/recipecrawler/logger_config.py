import logging
from datetime import datetime

def setup_logger(name, log_dir='', level=logging.INFO):
    """Function to setup a logger with console and file handlers."""
    # Create a logger
    logger = logging.getLogger(name)
    logger.setLevel(level)

    # Check if the logger already has handlers
    if not logger.hasHandlers():
        # Create handlers
        console_handler = logging.StreamHandler()

        # Set level for handlers
        console_handler.setLevel(level)

        # Create formatters and add them to handlers
        console_formatter = logging.Formatter('%(asctime)s - %(levelname)s - %(message)s')

        console_handler.setFormatter(console_formatter)

        # Add handlers to the logger
        logger.addHandler(console_handler)

    return logger