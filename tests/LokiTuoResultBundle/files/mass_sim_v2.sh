
mission1="Supremacy Mutant-10"
missionName1="Supremacy Mutant-10"
resultFile="result.json"
Member="loki"
MemberGuild="CTN"
MemberGuildId="null"
Inventory="Malika, Stonewall Garrison, Inheritor of Hope"
Deck="Malika, Stonewall Garrison, Inheritor of Hope"

myStructures=""
enemyStructures=""
iterations=10000

echo Number of sims pending : 1


echo "{"  > ./${resultFile}
echo "\"version\": 2,"   >> ./${resultFile}
echo "   \"bge\": null,">> ./${resultFile}
echo "   \"type\": \"climb\","  >> ./${resultFile}
echo "   \"ordered\": true,"  >> ./${resultFile}
echo "   \"surge\": false,"  >> ./${resultFile}
echo "   \"missions\": ["  >> ./${resultFile}echo "    {" >> ./${resultFile}
echo "      \"name\": \"${mission1}\",">> ./${resultFile}
echo "      \"myStructures\": \"${myStructures}\", " >> ./${resultFile}
echo "      \"enemyStructures\": \"${enemyStructures}\", ">> ./${resultFile}
echo "      \"results\": [ ">> ./${resultFile}
echo "        {">> ./${resultFile}
echo "          \"player\": \"$Member\",">> ./${resultFile}
echo "          \"player_id\": null,">> ./${resultFile}
echo "          \"guild\": \"$MemberGuild\",">> ./${resultFile}
echo "          \"guild_id\": $MemberGuildId,">> ./${resultFile}
echo "member name $Member against ${missionName1}"
./tuo "${Deck}" "${mission1}" -o="$Inventory" yf "${myStructures}" ef "${enemyStructures}" -r  climb ${iterations}  > ./tempRes.txt
Result=$(tail -1 ./tempRes.txt | head -1)
echo ${Result}
echo "          \"result\": \"${Result}\"">> ./${resultFile}
echo "       }">> ./${resultFile}
echo "      ]">> ./${resultFile}
echo "    }" >> ./${resultFile}
echo "  ]" >> ./${resultFile}
echo "}" >> ./${resultFile}