namespace FavorisApi.Models
{
    public class Favoris
    {
        public int Id { get; set; }
        public int? UtilisateurId { get; set; }
        public int? ProduitId { get; set; }
    }

    public class CreateFavorisRequest
    {
        public int UtilisateurId { get; set; }
        public int ProduitId { get; set; }
    }

    public class FavorisResponse
    {
        public int Id { get; set; }
        public int UtilisateurId { get; set; }
        public int ProduitId { get; set; }
        public DateTime CreatedAt { get; set; }
    }
}
