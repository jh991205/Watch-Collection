# Watch Collection Website

Welcome to the Watch Collection Website repository! This website is built using PHP and uses SQL as its database. The website allows users to view a collection of watches along with basic information such as price, brand, and tags associated with each watch. Additionally, it features a login system that differentiates between normal users and admins, granting different privileges to each role.

## Getting Started

To run the website locally, follow the steps below:

1. Make sure you have PHP and a SQL database (e.g., MySQL) installed on your local machine.

2. Clone this repository to your local machine using the following command:

```
git clone https://github.com/jh991205/Watch-Collection
```

3. Check out the login details for the different users are included as comments in the SQL file.

4. Start a local PHP server and direct your browser to `localhost:8000`.

## User Roles

1. **Normal Users:**
   - View the watch collection and basic information about each watch.
   - Add tags to different watches.
   - Add tags that already exist for the watches.

2. **Admins:**
   - All privileges of normal users.
   - Add new watches to the collection.
   - Delete watches from the collection.
   - Create additional tags for watches.

## Database Structure

The database contains the following tables:

- `watches`: Stores information about each watch, including `id`, `price`, `brand`, etc.
- `tags`: Contains the available tags for watches.
- `watch_tags`: A junction table linking watches to their corresponding tags.

## License

This project is licensed under the [MIT License](LICENSE).

Happy collecting!
