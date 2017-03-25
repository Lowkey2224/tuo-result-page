guild="CTN"
mission1="Supremacy Mutant-10"
missionName1="Supremacy Mutant-10"
resultFile="result.txt"
echo "${guild} Results 1,3,6-24"
echo "${guild} Results 1,3,6-24" > ./${resultFile}
Member="loki"
Inventory="Malika, Stonewall Garrison, Inheritor of Hope"
Deck="Malika, Stonewall Garrison, Inheritor of Hope"

myStructures=""
enemyStructures=""
iterations=10000

echo Number of sims pending : 1
echo "member name $Member@%s against ${missionName1}"
echo "member name $Member@%s against ${missionName1}" >> ./${resultFile}
./tuo "${Deck}" "${mission1}" -o="$Inventory" yf "${myStructures}" ef "${enemyStructures}" -r  climb ${iterations}  > ./tempRes.txt
tail -1 ./tempRes.txt | head -1 >> ./${resultFile}


