import json

def flatten_json(data, parent_key='', sep='.'):
    """
    Flatten a nested JSON file into a single level dictionary recursively.
    """
    items = {}
    if isinstance(data, dict):
        for k, v in data.items():
            new_key = f"{parent_key}{sep}{k}" if parent_key else k
            if isinstance(v, dict):
                items.update(flatten_json(v, new_key, sep=sep))
            else:
                items[new_key] = v
    elif isinstance(data, list):
        for idx, item in enumerate(data):
            items.update(flatten_json(item, f"{parent_key}{sep}{idx}", sep=sep))
    else:
        items[parent_key] = data
    return items

def main():
    with open('en.json', 'r') as file:
        data = json.load(file)
    
    flattened_data = flatten_json(data)

    with open('en.json', 'w') as file:
        file.write("{\n")
        for key, value in flattened_data.items():
            file.write(f'  "{key}": {json.dumps(value)},\n')
        file.write("}\n")

if __name__ == "__main__":
    main()
