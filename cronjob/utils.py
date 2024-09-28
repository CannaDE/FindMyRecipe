import re

# Funktion zum Konvertieren der ISO 8601-Dauer (z.B. PT20M, PT1H30M) in Minuten
def iso8601_to_minutes(iso_duration):
    hours = 0
    minutes = 0

    # Extrahieren der Stunden und Minuten aus der Dauer
    match = re.match(r'PT(?:(\d+)H)?(?:(\d+)M)?', iso_duration)
    if match:
        if match.group(1):
            hours = int(match.group(1))
        if match.group(2):
            minutes = int(match.group(2))
    
    return hours * 60 + minutes

# Funktion zum Konvertieren von Minuten zurück in das ISO 8601-Dauerformat
def minutes_to_iso8601(minutes):
    hours, minutes = divmod(minutes, 60)
    iso_duration = 'PT'
    
    if hours > 0:
        iso_duration += f'{hours}H'
    if minutes > 0:
        iso_duration += f'{minutes}M'
    
    return iso_duration

# Funktion zum Addieren zweier ISO 8601-Dauerformate
def add_durations(duration1, duration2):
    minutes1 = iso8601_to_minutes(duration1)
    minutes2 = iso8601_to_minutes(duration2)
    
    total_minutes = minutes1 + minutes2
    
    # Konvertieren der Gesamtminuten zurück in das ISO 8601-Format
    return minutes_to_iso8601(total_minutes)

# Funktion, um die Zeitangaben aus HTML zu extrahieren
def extract_time_from_html(soup, time_property):
    meta_tag = soup.find('meta', itemprop=time_property)
    if meta_tag and 'content' in meta_tag.attrs:
        return meta_tag['content']
    return None

# Funktion, um die Zeitdauer in Minuten zu berechnen
def duration_to_minutes(duration):
    if not duration:  # Wenn duration None oder leer ist
        return 0

    hours = 0
    minutes = 0

    # Regex für ISO 8601 Dauerformat: z.B. PT1H30M, PT20M
    match = re.match(r'PT(?:(\d+)H)?(?:(\d+)M)?', duration)
    if match:
        if match.group(1):  # Stunden (optional)
            hours = int(match.group(1))
        if match.group(2):  # Minuten (optional)
            minutes = int(match.group(2))
    
    # Gesamtdauer in Minuten (1 Stunde = 60 Minuten)
    total_minutes = hours * 60 + minutes
    return total_minutes

# Funktion zum Addieren von zwei Dauerformaten und Rückgabe in Minuten
def add_durations(duration1, duration2):
    minutes1 = duration_to_minutes(duration1)
    minutes2 = duration_to_minutes(duration2)
    
    total_minutes = minutes1 + minutes2
    return total_minutes
