using Dapper;
using FavorisApi.Models;
using Microsoft.Data.Sqlite;

namespace FavorisApi.Services
{
    public class FavorisService : IFavorisService
    {
        private readonly string _connectionString;

        public FavorisService(IConfiguration configuration)
        {
            _connectionString = configuration.GetConnectionString("DefaultConnection") ?? 
                throw new InvalidOperationException("Connection string 'DefaultConnection' not found.");
        }

        private SqliteConnection CreateConnection()
        {
            return new SqliteConnection(_connectionString);
        }

        public async Task<IEnumerable<Favoris>> GetAllFavorisAsync()
        {
            using var connection = CreateConnection();
            const string sql = "SELECT id as Id, utilisateur_id as UtilisateurId, produit_id as ProduitId FROM favoris";
            return await connection.QueryAsync<Favoris>(sql);
        }

        public async Task<IEnumerable<Favoris>> GetFavorisByUtilisateurAsync(int utilisateurId)
        {
            using var connection = CreateConnection();
            const string sql = "SELECT id as Id, utilisateur_id as UtilisateurId, produit_id as ProduitId FROM favoris WHERE utilisateur_id = @utilisateurId";
            return await connection.QueryAsync<Favoris>(sql, new { utilisateurId });
        }

        public async Task<Favoris?> GetFavorisAsync(int id)
        {
            using var connection = CreateConnection();
            const string sql = "SELECT id as Id, utilisateur_id as UtilisateurId, produit_id as ProduitId FROM favoris WHERE id = @id";
            return await connection.QueryFirstOrDefaultAsync<Favoris>(sql, new { id });
        }

        public async Task<Favoris?> CreateFavorisAsync(CreateFavorisRequest request)
        {
            // Vérifier si le favori existe déjà
            if (await FavorisExistsAsync(request.UtilisateurId, request.ProduitId))
            {
                return null; // Le favori existe déjà
            }

            using var connection = CreateConnection();
            const string sql = @"
                INSERT INTO favoris (utilisateur_id, produit_id) 
                VALUES (@utilisateurId, @produitId);
                SELECT last_insert_rowid();";
            
            var newId = await connection.QuerySingleAsync<int>(sql, new 
            { 
                utilisateurId = request.UtilisateurId, 
                produitId = request.ProduitId 
            });

            return new Favoris
            {
                Id = newId,
                UtilisateurId = request.UtilisateurId,
                ProduitId = request.ProduitId
            };
        }

        public async Task<bool> DeleteFavorisAsync(int id)
        {
            using var connection = CreateConnection();
            const string sql = "DELETE FROM favoris WHERE id = @id";
            var affectedRows = await connection.ExecuteAsync(sql, new { id });
            return affectedRows > 0;
        }

        public async Task<bool> DeleteFavorisByUserAndProductAsync(int utilisateurId, int produitId)
        {
            using var connection = CreateConnection();
            const string sql = "DELETE FROM favoris WHERE utilisateur_id = @utilisateurId AND produit_id = @produitId";
            var affectedRows = await connection.ExecuteAsync(sql, new { utilisateurId, produitId });
            return affectedRows > 0;
        }

        public async Task<bool> FavorisExistsAsync(int utilisateurId, int produitId)
        {
            using var connection = CreateConnection();
            const string sql = "SELECT COUNT(*) FROM favoris WHERE utilisateur_id = @utilisateurId AND produit_id = @produitId";
            var count = await connection.QuerySingleAsync<int>(sql, new { utilisateurId, produitId });
            return count > 0;
        }

        public async Task<int> GetFavorisCountByProductAsync(int produitId)
        {
            using var connection = CreateConnection();
            const string sql = "SELECT COUNT(*) FROM favoris WHERE produit_id = @produitId";
            return await connection.QuerySingleAsync<int>(sql, new { produitId });
        }
    }
}
