using Microsoft.AspNetCore.Mvc;
using FavorisApi.Models;
using FavorisApi.Services;

namespace FavorisApi.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class FavorisController : ControllerBase
    {
        private readonly IFavorisService _favorisService;
        private readonly ILogger<FavorisController> _logger;

        public FavorisController(IFavorisService favorisService, ILogger<FavorisController> logger)
        {
            _favorisService = favorisService;
            _logger = logger;
        }

        /// <summary>
        /// Récupère tous les favoris
        /// </summary>
        [HttpGet]
        public async Task<ActionResult<IEnumerable<Favoris>>> GetAllFavoris()
        {
            try
            {
                var favoris = await _favorisService.GetAllFavorisAsync();
                return Ok(favoris);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Erreur lors de la récupération de tous les favoris");
                return StatusCode(500, "Erreur interne du serveur");
            }
        }

        /// <summary>
        /// Récupère les favoris d'un utilisateur spécifique
        /// </summary>
        [HttpGet("user/{utilisateurId}")]
        public async Task<ActionResult<IEnumerable<Favoris>>> GetFavorisByUtilisateur(int utilisateurId)
        {
            try
            {
                var favoris = await _favorisService.GetFavorisByUtilisateurAsync(utilisateurId);
                return Ok(favoris);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Erreur lors de la récupération des favoris pour l'utilisateur {UtilisateurId}", utilisateurId);
                return StatusCode(500, "Erreur interne du serveur");
            }
        }

        /// <summary>
        /// Récupère un favori par son ID
        /// </summary>
        [HttpGet("{id}")]
        public async Task<ActionResult<Favoris>> GetFavoris(int id)
        {
            try
            {
                var favoris = await _favorisService.GetFavorisAsync(id);
                if (favoris == null)
                {
                    return NotFound($"Favori avec l'ID {id} non trouvé");
                }
                return Ok(favoris);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Erreur lors de la récupération du favori {Id}", id);
                return StatusCode(500, "Erreur interne du serveur");
            }
        }

        /// <summary>
        /// Crée un nouveau favori
        /// </summary>
        [HttpPost]
        public async Task<ActionResult<Favoris>> CreateFavoris([FromBody] CreateFavorisRequest request)
        {
            try
            {
                if (request.UtilisateurId <= 0 || request.ProduitId <= 0)
                {
                    return BadRequest("L'ID utilisateur et l'ID produit doivent être supérieurs à 0");
                }

                var favoris = await _favorisService.CreateFavorisAsync(request);
                if (favoris == null)
                {
                    return Conflict("Ce favori existe déjà");
                }

                return CreatedAtAction(nameof(GetFavoris), new { id = favoris.Id }, favoris);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Erreur lors de la création du favori");
                return StatusCode(500, "Erreur interne du serveur");
            }
        }

        /// <summary>
        /// Supprime un favori par son ID
        /// </summary>
        [HttpDelete("{id}")]
        public async Task<IActionResult> DeleteFavoris(int id)
        {
            try
            {
                var success = await _favorisService.DeleteFavorisAsync(id);
                if (!success)
                {
                    return NotFound($"Favori avec l'ID {id} non trouvé");
                }
                return NoContent();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Erreur lors de la suppression du favori {Id}", id);
                return StatusCode(500, "Erreur interne du serveur");
            }
        }

        /// <summary>
        /// Supprime un favori par utilisateur et produit
        /// </summary>
        [HttpDelete("user/{utilisateurId}/product/{produitId}")]
        public async Task<IActionResult> DeleteFavorisByUserAndProduct(int utilisateurId, int produitId)
        {
            try
            {
                var success = await _favorisService.DeleteFavorisByUserAndProductAsync(utilisateurId, produitId);
                if (!success)
                {
                    return NotFound($"Aucun favori trouvé pour l'utilisateur {utilisateurId} et le produit {produitId}");
                }
                return NoContent();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Erreur lors de la suppression du favori pour l'utilisateur {UtilisateurId} et le produit {ProduitId}", utilisateurId, produitId);
                return StatusCode(500, "Erreur interne du serveur");
            }
        }

        /// <summary>
        /// Vérifie si un favori existe pour un utilisateur et un produit
        /// </summary>
        [HttpGet("exists/user/{utilisateurId}/product/{produitId}")]
        public async Task<ActionResult<bool>> FavorisExists(int utilisateurId, int produitId)
        {
            try
            {
                var exists = await _favorisService.FavorisExistsAsync(utilisateurId, produitId);
                return Ok(new { exists });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Erreur lors de la vérification de l'existence du favori");
                return StatusCode(500, "Erreur interne du serveur");
            }
        }

        /// <summary>
        /// Récupère le nombre de favoris pour un produit spécifique
        /// </summary>
        [HttpGet("count/product/{produitId}")]
        public async Task<ActionResult<int>> GetFavorisCountByProduct(int produitId)
        {
            try
            {
                var count = await _favorisService.GetFavorisCountByProductAsync(produitId);
                return Ok(new { count });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Erreur lors de la récupération du nombre de favoris pour le produit {ProduitId}", produitId);
                return StatusCode(500, "Erreur interne du serveur");
            }
        }
    }
}
