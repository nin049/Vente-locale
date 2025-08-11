using FavorisApi.Models;

namespace FavorisApi.Services
{
    public interface IFavorisService
    {
        Task<IEnumerable<Favoris>> GetAllFavorisAsync();
        Task<IEnumerable<Favoris>> GetFavorisByUtilisateurAsync(int utilisateurId);
        Task<Favoris?> GetFavorisAsync(int id);
        Task<Favoris?> CreateFavorisAsync(CreateFavorisRequest request);
        Task<bool> DeleteFavorisAsync(int id);
        Task<bool> DeleteFavorisByUserAndProductAsync(int utilisateurId, int produitId);
        Task<bool> FavorisExistsAsync(int utilisateurId, int produitId);
        Task<int> GetFavorisCountByProductAsync(int produitId);
    }
}
