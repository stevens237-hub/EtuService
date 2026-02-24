import redis
import sys

# Connexion à Redis (qui tourne dans WSL ou en local)
r = redis.Redis(host='127.0.0.1', port=6379, db=0, decode_responses=True)

# Constantes
MAX_CONNEXIONS = 10
FENETRE_SECONDES = 600  # 10 minutes = 600 secondes

def verifier_connexion(email):
    cle = f"connexions:{email}"  # Ex: "connexions:alice@test.com"

    # Récupère le compteur actuel
    compteur = r.get(cle)

    if compteur is None:
        # Première connexion : on crée la clé avec valeur 1
        r.set(cle, 1, ex=FENETRE_SECONDES)
        print(f"AUTORISE|1|{email}")

    elif int(compteur) < MAX_CONNEXIONS:
        # Sous la limite : on incrémente
        nouveau = r.incr(cle)
        print(f"AUTORISE|{nouveau}|{email}")

    else:
        # Limite atteinte
        ttl = r.ttl(cle)  # Temps restant avant reset
        print(f"REFUSE|{compteur}|{email}|reset_dans:{ttl}s")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("ERREUR|email manquant")
        sys.exit(1)

    email = sys.argv[1]
    verifier_connexion(email)