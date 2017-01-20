<?php
if (elgg_is_xhr()) {  // This is an Ajax call!

    $user_guid = get_input('guid');
    $user = get_user($user_guid);

    $section = get_input('section');
    $error = false;

    switch ($section) {

        case "profile":
        
            $profile_fields = get_input('profile');
            $social_media = get_input('social_media');
            $error_message = '';


            foreach ( $profile_fields as $f => $v ) {

                // for each profile field
                switch ($f) {
                                        
                    case 'email': // check if email field is valid
                        if (elgg_is_active_plugin('c_email_extensions'))
                            elgg_load_library('c_ext_lib');

                        $isValid = false;
                        trim($v);


                        if (!$v) { // check if email field is empty
                            register_error(elgg_echo('gcc_profile:error').elgg_echo('gcc_profile:missingemail'));
                            return true;

                        
                        } else { // check if the email is in the list of exceptions
                            $user_email = explode('@', $v);
                            $list_of_domains = getExtension();
                            

                            $query = "SELECT * FROM email_extensions WHERE ext = '{$user_email[1]}'";
                            $result = get_data($query);
                            if (count($result) < 0)
                                $error_message = elgg_echo('gcc_profile:error').elgg_echo('gcc_profile:notaccepted');
                            else
                                $isValid = true;




                            if (!$isValid) { // check if domain is gc.ca
                                $govt_domain = explode('.',$user_email[1]);
                                $govt_domain_len = count($govt_domain) - 1;                           

                                if ($govt_domain[$govt_domain_len - 1].'.'.$govt_domain[$govt_domain_len] === 'gc.ca') {
                                    $isValid = true;
                                } else {
                                    $isValid = false;
                                    $error_message = elgg_echo('gcc_profile:error').elgg_echo('gcc_profile:notaccepted');
                                }
                            }
                        } // end if email field

                        if (!$isValid) {
                            register_error($error_message);
                            return true;
                        }

                        $user->set($f, $v);
                        break;


                    case 'federal': // check for valid department if applicable
                        if ($user->user_type === 'federal') {

                            /*
                    		$obj = elgg_get_entities(array(
            						'type' => 'object',
            						'subtype' => 'dept_list',
            						'owner_guid' => 0
            				));

            				$departmentsEn = json_decode($obj[0]->deptsEn, true);
            				$provinces['pov-alb'] = 'Government of Alberta';
            				$provinces['pov-bc'] = 'Government of British Columbia';
            				$provinces['pov-man'] = 'Government of Manitoba';
            				$provinces['pov-nb'] = 'Government of New Brunswick';
            				$provinces['pov-nfl'] = 'Government of Newfoundland and Labrador';
            				$provinces['pov-ns'] = 'Government of Nova Scotia';
            				$provinces['pov-nwt'] = 'Government of Northwest Territories';
            				$provinces['pov-nun'] = 'Government of Nunavut';
            				$provinces['pov-ont'] = 'Government of Ontario';
            				$provinces['pov-pei'] = 'Government of Prince Edward Island';
            				$provinces['pov-que'] = 'Government of Quebec';
            				$provinces['pov-sask'] = 'Government of Saskatchewan';
            				$provinces['pov-yuk'] = 'Government of Yukon';
            				$departmentsEn = array_merge($departmentsEn,$provinces);
            				
            				$departmentsFr = json_decode($obj[0]->deptsFr, true);
            				$provincesFr['pov-alb'] = "Gouvernement de l'Alberta";
            				$provincesFr['pov-bc'] = 'Gouvernement de la Colombie-Britannique';
            				$provincesFr['pov-man'] = 'Gouvernement du Manitoba';
            				$provincesFr['pov-nb'] = 'Gouvernement du Nouveau-Brunswick';
            				$provincesFr['pov-nfl'] = 'Gouvernement de Terre-Neuve-et-Labrador';
            				$provincesFr['pov-ns'] = 'Gouvernement de la Nouvelle-Écosse';
            				$provincesFr['pov-nwt'] = 'Gouvernement du Territoires du Nord-Ouest';
            				$provincesFr['pov-nun'] = 'Gouvernement du Nunavut';
            				$provincesFr['pov-ont'] = "Gouvernement de l'Ontario";
            				$provincesFr['pov-pei'] = "Gouvernement de l'Île-du-Prince-Édouard";
            				$provincesFr['pov-que'] = 'Gouvernement du Québec';
            				$provincesFr['pov-sask'] = 'Gouvernement de Saskatchewan';
            				$provincesFr['pov-yuk'] = 'Gouvernement du Yukon';
            				$departmentsFr = array_merge($departmentsFr,$provincesFr);
            				
            				if (get_current_language() == 'en')
            					$deptString = $departmentsEn[$v]." / ".$departmentsFr[$v];
            				else
            					$deptString = $departmentsFr[$v]." / ".$departmentsEn[$v];
            	
            				$user->set($f, $deptString);
                            */

                            $federal_departments = array();
                            if (get_current_language() == 'en'){
                                $federal_departments = array("Aboriginal Business Canada" => "Aboriginal Business Canada",
                                "Administrative Tribunals Support Service of Canada" => "Administrative Tribunals Support Service of Canada",
                                "Agriculture and Agri-Food Canada" => "Agriculture and Agri-Food Canada",
                                "Atlantic Canada Opportunities Agency" => "Atlantic Canada Opportunities Agency",
                                "Atlantic Pilotage Authority Canada" => "Atlantic Pilotage Authority Canada",
                                "Atomic Energy of Canada Limited" => "Atomic Energy of Canada Limited",
                                "Auditor General of Canada (Office of the)" => "Auditor General of Canada (Office of the)",
                                "Bank of Canada" => "Bank of Canada",
                                "Blue Water Bridge Canada" => "Blue Water Bridge Canada",
                                "Business Development Bank of Canada" => "Business Development Bank of Canada",
                                "Canada Agricultural Review Tribunal" => "Canada Agricultural Review Tribunal",
                                "Canada Agriculture and Food Museum" => "Canada Agriculture and Food Museum",
                                "Canada Aviation and Space Museum" => "Canada Aviation and Space Museum",
                                "Canada Border Services Agency" => "Canada Border Services Agency",
                                "Canada Centre for Inland Waters" => "Canada Centre for Inland Waters",
                                "Canada Council for the Arts" => "Canada Council for the Arts",
                                "Canada Deposit Insurance Corporation" => "Canada Deposit Insurance Corporation",
                                "Canada Development Investment Corporation" => "Canada Development Investment Corporation",
                                "Canada Economic Development for Quebec Regions" => "Canada Economic Development for Quebec Regions",
                                "Canada Employment Insurance Commission" => "Canada Employment Insurance Commission",
                                "Canada Firearms Centre" => "Canada Firearms Centre",
                                "Canada Gazette" => "Canada Gazette",
                                "Canada Industrial Relations Board" => "Canada Industrial Relations Board",
                                "Canada Lands Company Limited" => "Canada Lands Company Limited",
                                "Canada Mortgage and Housing Corporation" => "Canada Mortgage and Housing Corporation",
                                "Canada Pension Plan Investment Board" => "Canada Pension Plan Investment Board",
                                "Canada Post" => "Canada Post",
                                "Canada Research Chairs" => "Canada Research Chairs",
                                "Canada Revenue Agency" => "Canada Revenue Agency",
                                "Canada School of Public Service" => "Canada School of Public Service",
                                "Canada Science and Technology Museum Corporation" => "Canada Science and Technology Museum Corporation",
                                "Canadian Air Transport Security Authority" => "Canadian Air Transport Security Authority",
                                "Canadian Army" => "Canadian Army",
                                "Canadian Cadet Organizations" => "Canadian Cadet Organizations",
                                "Canadian Centre for Occupational Health and Safety" => "Canadian Centre for Occupational Health and Safety",
                                "Canadian Coast Guard" => "Canadian Coast Guard",
                                "Canadian Commercial Corporation" => "Canadian Commercial Corporation",
                                "Canadian Conservation Institute" => "Canadian Conservation Institute",
                                "Canadian Cultural Property Export Review Board" => "Canadian Cultural Property Export Review Board",
                                "Canadian Dairy Commission" => "Canadian Dairy Commission",
                                "Canadian Environmental Assessment Agency" => "Canadian Environmental Assessment Agency",
                                "Canadian Food Inspection Agency" => "Canadian Food Inspection Agency",
                                "Canadian Forces Housing Agency" => "Canadian Forces Housing Agency",
                                "Canadian General Standards Board" => "Canadian General Standards Board",
                                "Canadian Grain Commission" => "Canadian Grain Commission",
                                "Canadian Heritage" => "Canadian Heritage",
                                "Canadian Heritage Information Network" => "Canadian Heritage Information Network",
                                "Canadian Human Rights Commission" => "Canadian Human Rights Commission",
                                "Canadian Institutes of Health Research" => "Canadian Institutes of Health Research",
                                "Canadian Intellectual Property Office" => "Canadian Intellectual Property Office",
                                "Canadian Intergovernmental Conference Secretariat" => "Canadian Intergovernmental Conference Secretariat",
                                "Canadian International Trade Tribunal" => "Canadian International Trade Tribunal",
                                "Canadian Judicial Council" => "Canadian Judicial Council",
                                "Canadian Museum for Human Rights" => "Canadian Museum for Human Rights",
                                "Canadian Museum of Contemporary Photography" => "Canadian Museum of Contemporary Photography",
                                "Canadian Museum of History" => "Canadian Museum of History",
                                "Canadian Museum of Immigration at Pier 21" => "Canadian Museum of Immigration at Pier 21",
                                "Canadian Museum of Nature" => "Canadian Museum of Nature",
                                "Canadian Northern Economic Development Agency" => "Canadian Northern Economic Development Agency",
                                "Canadian Nuclear Safety Commission" => "Canadian Nuclear Safety Commission",
                                "Canadian Pari-Mutuel Agency" => "Canadian Pari-Mutuel Agency",
                                "Canadian Police College" => "Canadian Police College",
                                "Canadian Race Relations Foundation" => "Canadian Race Relations Foundation",
                                "Canadian Radio-Television and Telecommunications Commission" => "Canadian Radio-Television and Telecommunications Commission",
                                "Canadian Security Intelligence Service" => "Canadian Security Intelligence Service",
                                "Canadian Space Agency" => "Canadian Space Agency",
                                "Canadian Tourism Commission" => "Canadian Tourism Commission",
                                "Canadian Trade Commissioner Service" => "Canadian Trade Commissioner Service",
                                "Canadian Transportation Agency" => "Canadian Transportation Agency",
                                "Canadian War Museum" => "Canadian War Museum",
                                "Chief Electoral Officer (Office of the)" => "Chief Electoral Officer (Office of the)",
                                "Civilian Review and Complaints Commission for the RCMP" => "Civilian Review and Complaints Commission for the RCMP",
                                "Clerk of the Privy Council" => "Clerk of the Privy Council",
                                "Commissioner for Federal Judicial Affairs Canada (Office of the)" => "Commissioner for Federal Judicial Affairs Canada (Office of the)",
                                "Commissioner of Lobbying of Canada (Office of the)" => "Commissioner of Lobbying of Canada (Office of the)",
                                "Commissioner of Official Languages (Office of the)" => "Commissioner of Official Languages (Office of the)",
                                "Communications Research Centre Canada" => "Communications Research Centre Canada",
                                "Communications Security Establishment Canada" => "Communications Security Establishment Canada",
                                "Communications Security Establishment Commissioner (Office of the)" => "Communications Security Establishment Commissioner (Office of the)",
                                "Competition Bureau Canada" => "Competition Bureau Canada",
                                "Competition Tribunal" => "Competition Tribunal",
                                "Conflict of Interest and Ethics Commissioner (Office of the)" => "Conflict of Interest and Ethics Commissioner (Office of the)",
                                "Copyright Board Canada" => "Copyright Board Canada",
                                "CORCAN" => "CORCAN",
                                "Correctional Investigator Canada" => "Correctional Investigator Canada",
                                "Correctional Service Canada" => "Correctional Service Canada",
                                "Courts Administration Service" => "Courts Administration Service",
                                "Currency Museum" => "Currency Museum",
                                "Defence Construction Canada" => "Defence Construction Canada",
                                "Defence Research and Development Canada" => "Defence Research and Development Canada",
                                "Democratic Institutions" => "Democratic Institutions",
                                "Elections Canada" => "Elections Canada",
                                "Employment and Social Development Canada" => "Employment and Social Development Canada",
                                "Environment and Climate Change Canada" => "Environment and Climate Change Canada",
                                "Environmental Protection Review Canada" => "Environmental Protection Review Canada",
                                "Export Development Canada" => "Export Development Canada",
                                "Farm Credit Canada" => "Farm Credit Canada",
                                "Farm Products Council of Canada" => "Farm Products Council of Canada",
                                "Federal Bridge Corporation" => "Federal Bridge Corporation",
                                "Federal Court of Appeal" => "Federal Court of Appeal",
                                "Federal Court of Canada" => "Federal Court of Canada",
                                "Federal Economic Development Agency for Southern Ontario" => "Federal Economic Development Agency for Southern Ontario",
                                "Federal Economic Development Initiative for Northern Ontario (FedNor)" => "Federal Economic Development Initiative for Northern Ontario (FedNor)",
                                "Federal Ombudsman for Victims Of Crime (Office of the)" => "Federal Ombudsman for Victims Of Crime (Office of the)",
                                "Finance Canada (Department of)" => "Finance Canada (Department of)",
                                "Financial Consumer Agency of Canada" => "Financial Consumer Agency of Canada",
                                "Financial Transactions and Reports Analysis Centre of Canada" => "Financial Transactions and Reports Analysis Centre of Canada",
                                "Fisheries and Oceans Canada" => "Fisheries and Oceans Canada",
                                "Freshwater Fish Marketing Corporation" => "Freshwater Fish Marketing Corporation",
                                "Geographical Names Board of Canada" => "Geographical Names Board of Canada",
                                "Geomatics Canada" => "Geomatics Canada",
                                "Global Affairs Canada" => "Global Affairs Canada",
                                "Governor General of Canada" => "Governor General of Canada",
                                "Great Lakes Pilotage Authority Canada" => "Great Lakes Pilotage Authority Canada",
                                "Health Canada" => "Health Canada",
                                "Historic Sites and Monuments Board of Canada" => "Historic Sites and Monuments Board of Canada",
                                "Human Rights Tribunal of Canada" => "Human Rights Tribunal of Canada",
                                "Immigration and Refugee Board of Canada" => "Immigration and Refugee Board of Canada",
                                "Immigration, Refugees and Citizenship Canada" => "Immigration, Refugees and Citizenship Canada",
                                "Indian Oil and Gas Canada" => "Indian Oil and Gas Canada",
                                "Indian Residential Schools Truth and Reconciliation Commission" => "Indian Residential Schools Truth and Reconciliation Commission",
                                "Indigenous and Northern Affairs Canada" => "Indigenous and Northern Affairs Canada",
                                "Industrial Technologies Office" => "Industrial Technologies Office",
                                "Information Commissioner (Office of the)" => "Information Commissioner (Office of the)",
                                "Infrastructure Canada" => "Infrastructure Canada",
                                "Innovation, Science and Economic Development Canada" => "Innovation, Science and Economic Development Canada",
                                "Intergovernmental Affairs (Department of)" => "Intergovernmental Affairs (Department of)",
                                "International Development Research Centre" => "International Development Research Centre",
                                "Jacques Cartier and Champlain Bridges Inc." => "Jacques Cartier and Champlain Bridges Inc.",
                                "Justice Canada (Department of)" => "Justice Canada (Department of)",
                                "Labour Program" => "Labour Program",
                                "Laurentian Pilotage Authority Canada" => "Laurentian Pilotage Authority Canada",
                                "Leader of the Government in the House of Commons" => "Leader of the Government in the House of Commons",
                                "Library and Archives Canada" => "Library and Archives Canada",
                                "Marine Atlantic" => "Marine Atlantic",
                                "Measurement Canada" => "Measurement Canada",
                                "Military Grievances External Review Committee" => "Military Grievances External Review Committee",
                                "Military Police Complaints Commission of Canada" => "Military Police Complaints Commission of Canada",
                                "National Arts Centre" => "National Arts Centre",
                                "National Battlefields Commission" => "National Battlefields Commission",
                                "National Capital Commission" => "National Capital Commission",
                                "National Defence" => "National Defence",
                                "National Defence and the Canadian Forces Ombudsperson (Office of the)" => "National Defence and the Canadian Forces Ombudsperson (Office of the)",
                                "National Energy Board" => "National Energy Board",
                                "National Film Board" => "National Film Board",
                                "National Gallery of Canada" => "National Gallery of Canada",
                                "National Museum of Science and Technology" => "National Museum of Science and Technology",
                                "National Research Council Canada" => "National Research Council Canada",
                                "National Search and Rescue Secretariat" => "National Search and Rescue Secretariat",
                                "National Seniors Council" => "National Seniors Council",
                                "Natural Resources Canada" => "Natural Resources Canada",
                                "Natural Sciences and Engineering Research Canada" => "Natural Sciences and Engineering Research Canada",
                                "Northern Pipeline Agency Canada" => "Northern Pipeline Agency Canada",
                                "Occupational Health and Safety Tribunal Canada" => "Occupational Health and Safety Tribunal Canada",
                                "Pacific Pilotage Authority Canada" => "Pacific Pilotage Authority Canada",
                                "Parks Canada" => "Parks Canada",
                                "Parliament of Canada" => "Parliament of Canada",
                                "Parole Board of Canada" => "Parole Board of Canada",
                                "Passport Canada" => "Passport Canada",
                                "Patented Medicine Prices Review Board Canada" => "Patented Medicine Prices Review Board Canada",
                                "Polar Knowledge Canada" => "Polar Knowledge Canada",
                                "PPP Canada" => "PPP Canada",
                                "Prime Minister of Canada" => "Prime Minister of Canada",
                                "Privacy Commissioner (Office of the)" => "Privacy Commissioner (Office of the)",
                                "Privy Council Office" => "Privy Council Office",
                                "Procurement Ombudsman (Office of the)" => "Procurement Ombudsman (Office of the)",
                                "Public Health Agency of Canada" => "Public Health Agency of Canada",
                                "Public Prosecution Service of Canada" => "Public Prosecution Service of Canada",
                                "Public Safety Canada" => "Public Safety Canada",
                                "Public Sector Integrity Commissioner of Canada (Office of the)" => "Public Sector Integrity Commissioner of Canada (Office of the)",
                                "Public Sector Pension Investment Board" => "Public Sector Pension Investment Board",
                                "Public Servants Disclosure Protection Tribunal Canada" => "Public Servants Disclosure Protection Tribunal Canada",
                                "Public Service Commission of Canada" => "Public Service Commission of Canada",
                                "Public Service Labour Relations and Employment Board" => "Public Service Labour Relations and Employment Board",
                                "Public Services and Procurement Canada" => "Public Services and Procurement Canada",
                                "Receiver General for Canada" => "Receiver General for Canada",
                                "Registry of the Specific Claims Tribunal of Canada" => "Registry of the Specific Claims Tribunal of Canada",
                                "Ridley Terminals Inc." => "Ridley Terminals Inc.",
                                "Royal Canadian Air Force" => "Royal Canadian Air Force",
                                "Royal Canadian Mint" => "Royal Canadian Mint",
                                "Royal Canadian Mounted Police" => "Royal Canadian Mounted Police",
                                "Royal Canadian Mounted Police External Review Committee" => "Royal Canadian Mounted Police External Review Committee",
                                "Royal Canadian Navy" => "Royal Canadian Navy",
                                "Royal Military College of Canada" => "Royal Military College of Canada",
                                "Secretary to the Governor General (Office of the)" => "Secretary to the Governor General (Office of the)",
                                "Security Intelligence Review Committee" => "Security Intelligence Review Committee",
                                "Seniors" => "Seniors",
                                "Service Canada" => "Service Canada",
                                "Shared Services Canada" => "Shared Services Canada",
                                "Ship-Source Oil Pollution Fund" => "Ship-Source Oil Pollution Fund",
                                "Social Sciences and Humanities Research Council of Canada" => "Social Sciences and Humanities Research Council of Canada",
                                "Social Security Tribunal of Canada" => "Social Security Tribunal of Canada",
                                "Sport Canada" => "Sport Canada",
                                "Standards Council of Canada" => "Standards Council of Canada",
                                "Statistics Canada" => "Statistics Canada",
                                "Status of Women Canada" => "Status of Women Canada",
                                "Superintendent of Bankruptcy Canada (Office of the)" => "Superintendent of Bankruptcy Canada (Office of the)",
                                "Superintendent of Financial Institutions Canada (Office of the)" => "Superintendent of Financial Institutions Canada (Office of the)",
                                "Supreme Court of Canada" => "Supreme Court of Canada",
                                "Tax Court of Canada" => "Tax Court of Canada",
                                "Taxpayers' Ombudsman (Office of the)" => "Taxpayers' Ombudsman (Office of the)",
                                "Telefilm Canada" => "Telefilm Canada",
                                "Translation Bureau" => "Translation Bureau",
                                "Transport Canada" => "Transport Canada",
                                "Transportation Appeal Tribunal of Canada" => "Transportation Appeal Tribunal of Canada",
                                "Transportation Safety Board of Canada" => "Transportation Safety Board of Canada",
                                "Treasury Board of Canada Secretariat" => "Treasury Board of Canada Secretariat",
                                "Veterans Affairs Canada" => "Veterans Affairs Canada",
                                "Veterans' Ombudsman (Office of the)" => "Veterans' Ombudsman (Office of the)",
                                "Veterans Review and Appeal Board Canada" => "Veterans Review and Appeal Board Canada",
                                "VIA Rail Canada Inc." => "VIA Rail Canada Inc.",
                                "Virtual Museum of Canada" => "Virtual Museum of Canada",
                                "Western Economic Diversification Canada" => "Western Economic Diversification Canada");
                            } else {
                                $federal_departments = array("Aboriginal Business Canada" => "Entreprise autochtone Canada",
                                "Administrative Tribunals Support Service of Canada" => "Service canadien d'appui aux tribunaux administratifs",
                                "Agriculture and Agri-Food Canada" => "Agriculture et Agroalimentaire Canada",
                                "Atlantic Canada Opportunities Agency" => "Agence de promotion économique du Canada atlantique",
                                "Atlantic Pilotage Authority Canada" => "Administration de pilotage de l'Atlantique Canada",
                                "Atomic Energy of Canada Limited" => "Énergie atomique du Canada, Limitée",
                                "Auditor General of Canada (Office of the)" => "Bureau du vérificateur général du Canada",
                                "Bank of Canada" => "Banque du Canada",
                                "Blue Water Bridge Canada" => "Pont Blue Water Canada",
                                "Business Development Bank of Canada" => "Banque de développement du Canada",
                                "Canada Agricultural Review Tribunal" => "Commission de révision agricole du Canada",
                                "Canada Agriculture and Food Museum" => "Musée de l'agriculture et de l'alimentation du Canada",
                                "Canada Aviation and Space Museum" => "Musée de l'aviation et de l'espace du Canada",
                                "Canada Border Services Agency" => "Agence des services frontaliers du Canada",
                                "Canada Centre for Inland Waters" => "Centre canadien des eaux intérieures",
                                "Canada Council for the Arts" => "Conseil des arts du Canada",
                                "Canada Deposit Insurance Corporation" => "Société d'assurance-dépôts du Canada",
                                "Canada Development Investment Corporation" => "Corporation de développement des investissements du Canada",
                                "Canada Economic Development for Quebec Regions" => "Développement économique Canada pour les régions du Québec",
                                "Canada Employment Insurance Commission" => "Commission de l'assurance-emploi du Canada",
                                "Canada Firearms Centre" => "Centre des armes à feu Canada",
                                "Canada Gazette" => "Gazette du Canada",
                                "Canada Industrial Relations Board" => "Conseil canadien des relations industrielles",
                                "Canada Lands Company Limited" => "Société immobilière du Canada Limitée",
                                "Canada Mortgage and Housing Corporation" => "Société canadienne d'hypothèques et de logement",
                                "Canada Pension Plan Investment Board" => "Office d'investissement du régime de pensions du Canada",
                                "Canada Post" => "Postes Canada",
                                "Canada Research Chairs" => "Chaires de recherche du Canada",
                                "Canada Revenue Agency" => "Agence du revenu du Canada",
                                "Canada School of Public Service" => "École de la fonction publique du Canada",
                                "Canada Science and Technology Museum Corporation" => "Société des musées de sciences et technologies du Canada",
                                "Canadian Air Transport Security Authority" => "Administration canadienne de la sûreté du transport aérien",
                                "Canadian Army" => "Armée canadienne",
                                "Canadian Cadet Organizations" => "Organisations de cadets du Canada",
                                "Canadian Centre for Occupational Health and Safety" => "Centre canadien d'hygiène et de sécurité au travail",
                                "Canadian Coast Guard" => "Garde côtière canadienne",
                                "Canadian Commercial Corporation" => "Corporation commerciale canadienne",
                                "Canadian Conservation Institute" => "Institut canadien de conservation",
                                "Canadian Cultural Property Export Review Board" => "Commission canadienne d'examen des exportations de biens culturels",
                                "Canadian Dairy Commission" => "Commission canadienne du lait",
                                "Canadian Environmental Assessment Agency" => "Agence canadienne d'évaluation environnementale",
                                "Canadian Food Inspection Agency" => "Agence canadienne d'inspection des aliments",
                                "Canadian Forces Housing Agency" => "Agence de logement des Forces canadiennes",
                                "Canadian General Standards Board" => "Office des normes générales du Canada",
                                "Canadian Grain Commission" => "Commission canadienne des grains",
                                "Canadian Heritage" => "Patrimoine canadien",
                                "Canadian Heritage Information Network" => "Réseau canadien d'information sur le patrimoine",
                                "Canadian Human Rights Commission" => "Commission canadienne des droits de la personne",
                                "Canadian Institutes of Health Research" => "Instituts de recherche en santé du Canada",
                                "Canadian Intellectual Property Office" => "Office de la propriété intellectuelle du Canada",
                                "Canadian Intergovernmental Conference Secretariat" => "Secrétariat des conférences intergouvernementales canadiennes",
                                "Canadian International Trade Tribunal" => "Tribunal canadien du commerce extérieur",
                                "Canadian Judicial Council" => "Conseil canadien de la magistrature",
                                "Canadian Museum for Human Rights" => "Musée canadien pour les droits de la personne",
                                "Canadian Museum of Contemporary Photography" => "Musée canadien de la photographie contemporaine",
                                "Canadian Museum of History" => "Musée canadien de l'histoire",
                                "Canadian Museum of Immigration at Pier 21" => "Musée canadien de l'immigration du Quai 21",
                                "Canadian Museum of Nature" => "Musée canadien de la nature",
                                "Canadian Northern Economic Development Agency" => "Agence canadienne de développement économique du Nord",
                                "Canadian Nuclear Safety Commission" => "Commission canadienne de sûreté nucléaire",
                                "Canadian Pari-Mutuel Agency" => "Agence canadienne du pari mutuel",
                                "Canadian Police College" => "Collège canadien de police",
                                "Canadian Race Relations Foundation" => "Fondation canadienne des relations raciales",
                                "Canadian Radio-Television and Telecommunications Commission" => "Conseil de la radiodiffusion et des télécommunications canadiennes",
                                "Canadian Security Intelligence Service" => "Service canadien du renseignement de sécurité",
                                "Canadian Space Agency" => "Agence spatiale canadienne",
                                "Canadian Tourism Commission" => "Commission canadienne du tourisme",
                                "Canadian Trade Commissioner Service" => "Service des délégués commerciaux du Canada",
                                "Canadian Transportation Agency" => "Office des transports du Canada",
                                "Canadian War Museum" => "Musée canadien de la guerre",
                                "Chief Electoral Officer (Office of the)" => "Bureau du directeur général des élections",
                                "Civilian Review and Complaints Commission for the RCMP" => "Commission civile d'examen et de traitement des plaintes relatives à la GRC",
                                "Clerk of the Privy Council" => "Greffier du Conseil privé",
                                "Commissioner for Federal Judicial Affairs Canada (Office of the)" => "Commissariat à la magistrature fédérale Canada",
                                "Commissioner of Lobbying of Canada (Office of the)" => "Commissariat au lobbying du Canada",
                                "Commissioner of Official Languages (Office of the)" => "Commissariat aux langues officielles",
                                "Communications Research Centre Canada" => "Centre de recherches sur les communications Canada",
                                "Communications Security Establishment Canada" => "Centre de la sécurité des télécommunications Canada",
                                "Communications Security Establishment Commissioner (Office of the)" => "Bureau du commissaire du Centre de la sécurité des télécommunications",
                                "Competition Bureau Canada" => "Bureau de la concurrence Canada",
                                "Competition Tribunal" => "Tribunal de la concurrence",
                                "Conflict of Interest and Ethics Commissioner (Office of the)" => "Commissariat aux conflits d'intérêts et à l'éthique",
                                "Copyright Board Canada" => "Commission du droit d'auteur Canada",
                                "CORCAN" => "CORCAN",
                                "Correctional Investigator Canada" => "Enquêteur correctionnel Canada",
                                "Correctional Service Canada" => "Service correctionnel Canada",
                                "Courts Administration Service" => "Service administratif des tribunaux judiciaires",
                                "Currency Museum" => "Musée de la Banque du Canada",
                                "Defence Construction Canada" => "Construction de Défense Canada",
                                "Defence Research and Development Canada" => "Recherche et développement pour la Défense Canada",
                                "Democratic Institutions" => "Institutions démocratiques",
                                "Elections Canada" => "Élections Canada",
                                "Employment and Social Development Canada" => "Emploi et Développement social Canada",
                                "Environment and Climate Change Canada" => "Environnement et Changement climatique Canada",
                                "Environmental Protection Review Canada" => "Révision de la protection de l'environnement Canada",
                                "Export Development Canada" => "Exportation et développement Canada",
                                "Farm Credit Canada" => "Financement agricole Canada",
                                "Farm Products Council of Canada" => "Conseil des produits agricoles du Canada",
                                "Federal Bridge Corporation" => "Société des ponts fédéraux",
                                "Federal Court of Appeal" => "Cour d'appel fédérale",
                                "Federal Court of Canada" => "Cour fédérale",
                                "Federal Economic Development Agency for Southern Ontario" => "Agence fédérale de développement économique pour le Sud de l'Ontario",
                                "Federal Economic Development Initiative for Northern Ontario (FedNor)" => "Initiative fédérale de développement économique pour le Nord de l'Ontario (FedNor)",
                                "Federal Ombudsman for Victims Of Crime (Office of the)" => "Bureau de l'ombudsman fédéral des victimes d'actes criminels",
                                "Finance Canada (Department of)" => "Finances Canada, Ministère des",
                                "Financial Consumer Agency of Canada" => "Agence de la consommation en matière financière du Canada",
                                "Financial Transactions and Reports Analysis Centre of Canada" => "Centre d'analyse des opérations et déclarations financières du Canada",
                                "Fisheries and Oceans Canada" => "Pêches et Océans Canada",
                                "Freshwater Fish Marketing Corporation" => "Office de commercialisation du poisson d'eau douce",
                                "Geographical Names Board of Canada" => "Commission de toponymie du Canada",
                                "Geomatics Canada" => "Géomatique Canada",
                                "Global Affairs Canada" => "Affaires mondiales Canada",
                                "Governor General of Canada" => "Gouverneur général du Canada",
                                "Great Lakes Pilotage Authority Canada" => "Administration de pilotage des Grands Lacs Canada",
                                "Health Canada" => "Santé Canada",
                                "Historic Sites and Monuments Board of Canada" => "Commission des lieux et monuments historiques du Canada",
                                "Human Rights Tribunal of Canada" => "Tribunal des droits de la personne du Canada",
                                "Immigration and Refugee Board of Canada" => "Commission de l'immigration et du statut de réfugié du Canada",
                                "Immigration, Refugees and Citizenship Canada" => "Immigration, Réfugiés, et Citoyenneté Canada",
                                "Indian Oil and Gas Canada" => "Pétrole et gaz des Indiens du Canada",
                                "Indian Residential Schools Truth and Reconciliation Commission" => "Commission de vérité et de réconciliation relative aux pensionnats indiens",
                                "Indigenous and Northern Affairs Canada" => "Affaires autochtones et du Nord Canada",
                                "Industrial Technologies Office" => "Office des technologies industrielles",
                                "Information Commissioner (Office of the)" => "Commissariat à l'information au Canada",
                                "Infrastructure Canada" => "Infrastructure Canada",
                                "Innovation, Science and Economic Development Canada" => "Innovation, Sciences et Développement économique Canada",
                                "Intergovernmental Affairs (Department of)" => "Affaires intergouvernementales",
                                "International Development Research Centre" => "Centre de recherches pour le développement international",
                                "Jacques Cartier and Champlain Bridges Inc." => "Ponts Jacques-Cartier et Champlain Inc.",
                                "Justice Canada (Department of)" => "Justice Canada, Ministère de la",
                                "Labour Program" => "Programme du travail",
                                "Laurentian Pilotage Authority Canada" => "Administration de pilotage des Laurentides Canada",
                                "Leader of the Government in the House of Commons" => "Leader du gouvernement à la Chambre des communes",
                                "Library and Archives Canada" => "Bibliothèque et Archives Canada",
                                "Marine Atlantic" => "Marine Atlantique",
                                "Measurement Canada" => "Mesures Canada",
                                "Military Grievances External Review Committee" => "Comité externe d'examen des griefs militaires",
                                "Military Police Complaints Commission of Canada" => "Commission d'examen des plaintes concernant la police militaire du Canada",
                                "National Arts Centre" => "Centre national des arts",
                                "National Battlefields Commission" => "Commission des champs de bataille nationaux",
                                "National Capital Commission" => "Commission de la capitale nationale",
                                "National Defence" => "Défense nationale",
                                "National Defence and the Canadian Forces Ombudsperson (Office of the)" => "Ombudsman de la Défense nationale et des Forces canadiennes",
                                "National Energy Board" => "Office national de l'énergie",
                                "National Film Board" => "Office national du film",
                                "National Gallery of Canada" => "Musée des beaux-arts du Canada",
                                "National Museum of Science and Technology" => "Musées des sciences et de la technologie du Canada",
                                "National Research Council Canada" => "Conseil national de recherches Canada",
                                "National Search and Rescue Secretariat" => "Secrétariat national recherche et sauvetage",
                                "National Seniors Council" => "Conseil national des aînés",
                                "Natural Resources Canada" => "Ressources naturelles Canada",
                                "Natural Sciences and Engineering Research Canada" => "Conseil de recherches en sciences et en génie Canada",
                                "Northern Pipeline Agency Canada" => "Administration du pipe-line du Nord Canada",
                                "Occupational Health and Safety Tribunal Canada" => "Tribunal de santé et sécurité au travail Canada",
                                "Pacific Pilotage Authority Canada" => "Administration de pilotage du Pacifique Canada",
                                "Parks Canada" => "Parcs Canada",
                                "Parliament of Canada" => "Parlement du Canada",
                                "Parole Board of Canada" => "Commission des libérations conditionnelles du Canada",
                                "Passport Canada" => "Passeport Canada",
                                "Patented Medicine Prices Review Board Canada" => "Conseil d'examen du prix des médicaments brevetés Canada",
                                "Polar Knowledge Canada" => "Savoir polaire Canada",
                                "PPP Canada" => "PPP Canada",
                                "Prime Minister of Canada" => "Premier ministre du Canada",
                                "Privacy Commissioner (Office of the)" => "Commissariat à la protection de la vie privée au Canada",
                                "Privy Council Office" => "Bureau du Conseil privé",
                                "Procurement Ombudsman (Office of the)" => "Bureau de l'ombudsman de l'approvisionnement",
                                "Public Health Agency of Canada" => "Agence de la santé publique du Canada",
                                "Public Prosecution Service of Canada" => "Service des poursuites pénales du Canada",
                                "Public Safety Canada" => "Sécurité publique Canada",
                                "Public Sector Integrity Commissioner of Canada (Office of the)" => "Commissariat à l'intégrité du secteur public du Canada",
                                "Public Sector Pension Investment Board" => "Investissement des régimes de pensions du secteur public",
                                "Public Servants Disclosure Protection Tribunal Canada" => "Tribunal de la protection des fonctionnaires divulgateurs Canada",
                                "Public Service Commission of Canada" => "Commission de la fonction publique du Canada",
                                "Public Service Labour Relations and Employment Board" => "Commission des relations de travail et de l'emploi dans la fonction publique",
                                "Public Services and Procurement Canada" => "Services publics et Approvisionnement Canada",
                                "Receiver General for Canada" => "Receveur général du Canada",
                                "Registry of the Specific Claims Tribunal of Canada" => "Greffe du Tribunal des revendications particulières du Canada",
                                "Ridley Terminals Inc." => "Ridley Terminals Inc.",
                                "Royal Canadian Air Force" => "Aviation royale canadienne",
                                "Royal Canadian Mint" => "Monnaie royale canadienne",
                                "Royal Canadian Mounted Police" => "Gendarmerie royale du Canada",
                                "Royal Canadian Mounted Police External Review Committee" => "Comité externe d'examen de la Gendarmerie royale du Canada",
                                "Royal Canadian Navy" => "Marine royale canadienne",
                                "Royal Military College of Canada" => "Collège militaire royal du Canada",
                                "Secretary to the Governor General (Office of the)" => "Bureau du secrétaire du gouverneur général",
                                "Security Intelligence Review Committee" => "Comité de surveillance des activités de renseignement de sécurité",
                                "Seniors" => "Aînés",
                                "Service Canada" => "Service Canada",
                                "Shared Services Canada" => "Services partagés Canada",
                                "Ship-Source Oil Pollution Fund" => "Caisse d'indemnisation des dommages dus à la pollution par les hydrocarbures causée par les navires",
                                "Social Sciences and Humanities Research Council of Canada" => "Conseil de recherches en sciences humaines du Canada",
                                "Social Security Tribunal of Canada" => "Tribunal de la sécurité sociale du Canada",
                                "Sport Canada" => "Sport Canada",
                                "Standards Council of Canada" => "Conseil canadien des normes",
                                "Statistics Canada" => "Statistique Canada",
                                "Status of Women Canada" => "Condition féminine Canada",
                                "Superintendent of Bankruptcy Canada (Office of the)" => "Bureau du surintendant des faillites Canada",
                                "Superintendent of Financial Institutions Canada (Office of the)" => "Bureau du surintendant des institutions financières Canada",
                                "Supreme Court of Canada" => "Cour suprême du Canada",
                                "Tax Court of Canada" => "Cour canadienne de l'impôt",
                                "Taxpayers' Ombudsman (Office of the)" => "Bureau de l'ombudsman des contribuables",
                                "Telefilm Canada" => "Téléfilm Canada",
                                "Translation Bureau" => "Bureau de la traduction",
                                "Transport Canada" => "Transports Canada",
                                "Transportation Appeal Tribunal of Canada" => "Tribunal d'appel des transports du Canada",
                                "Transportation Safety Board of Canada" => "Bureau de la sécurité des transports du Canada",
                                "Treasury Board of Canada Secretariat" => "Secrétariat du Conseil du Trésor du Canada",
                                "Veterans Affairs Canada" => "Anciens Combattants Canada",
                                "Veterans' Ombudsman (Office of the)" => "Ombudsman des vétérans",
                                "Veterans Review and Appeal Board Canada" => "Tribunal des anciens combattants (révision et appel) Canada",
                                "VIA Rail Canada Inc." => "VIA Rail Canada Inc.",
                                "Virtual Museum of Canada" => "Musée virtuel du Canada",
                                "Western Economic Diversification Canada" => "Diversification de l'économie de l'Ouest Canada");
                            }
                            $user->set($f, $deptString);
                        }
                        break;

                    
                    case 'institution':
                        if ($user->user_type === 'student' || $user->user_type === 'academic') {
                            $query = "SELECT dept FROM email_extensions WHERE ext = '{$v}'";
                            $institution = get_data($query);
                            $user->set($f, $institution[0]->dept);
                        }
                        break;
                
                	//register_error($f);
                    
                    default:
                    $user->set($f, $v);
 

                    } // end switch statement
                } // end foreach loop (user profile)



                // save the social media information
                foreach ( $social_media as $f => $v ) {
                    $link = $v;
                    if (filter_var($link, FILTER_VALIDATE_URL) == false)
                        $user->set($f, $link);
                }

                //$user->micro = get_input('micro');
                $user->save();

            //forward($user->getURL());
            break;


        case 'about-me':
            //$user->description = get_input('description', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0001.');

            //error_log(print_r("access: " . get_input('access')));
            create_metadata($user_guid, 'description', get_input('description', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0001.'), 'text', 0, get_input('access'));

            $user->save();

            break;
        case 'education':
            $eguid = get_input('eguid', '');
            $delete = get_input('delete', '');
            $school = get_input('school', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0002.');
            $startdate = get_input('startdate', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0003.');
            $startyear = get_input('startyear');
            $enddate = get_input('enddate', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0004.');
            $endyear = get_input('endyear');
            $ongoing = get_input('ongoing');
            //$program = get_input('program', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0005.');
            $degree = get_input('degree');
            $field = get_input('field', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0006.');
            $access = get_input('access', 'ERROR: Ask your admin to grep: 5321GDS1111661353BB.');

            // create education object
            $education_guids = array();

            $education_list = $user->education;

            if ($delete != null && !is_array($delete)) {
                $delete = array( $delete );
            }

            if ( is_array($delete) ) {
                foreach ($delete as $delete_guid) {
                    if ($delete_guid != NULL) {

                        if ($delete = get_entity($delete_guid)) {
                            $delete->delete();
                        }
                        if (is_array($education_list)) {
                            if (($key = array_search($delete_guid, $education_list)) !== false) {
                                unset($education_list[$key]);
                            }
                        } elseif ($education_list == $delete_guid) {
                            $education_list = null;
                        }
                    }
                }
            }
            $user->education = $education_list;

            if ($eguid != null && !is_array($eguid)) {
                $eguid = array( $eguid );
            }
            //create new education entries
            if (is_array($eguid)) {
                foreach ($eguid as $k => $v) {


                    $validInput = true;

                    /*
                    if($ongoing[$k] == true){
                        $endyear[$k] = $startyear[$k];
                    }*/

                    if(trim( htmlentities($school[$k])) == '' || trim( htmlentities($degree[$k])) == '' || trim( htmlentities($field[$k])) == ''){
                        $validInput = false;
                        $error == true;
                    }
                    /*
                    if(trim( $endyear[$k]) < trim($startyear[$k])){
                        $validInput = false;
                        $error == true;
                    }
                    */
                    if($validInput == true){


                        if ($v == "new") {
                            $education = new ElggObject();
                            $education->subtype = "education";
                            $education->owner_guid = $user_guid;
                        } else {
                            $education = get_entity($v);
                        }

                        $education->title = htmlentities($school[$k]);
                        $education->description = htmlentities($degree[$k]);

                        $education->school = htmlentities($school[$k]);
                        $education->startdate = $startdate[$k];
                        $education->startyear = $startyear[$k];
                        $education->enddate = $enddate[$k];
                        $education->endyear = $endyear[$k];
                        $education->ongoing = $ongoing[$k];
                       
                        $education->degree = htmlentities($degree[$k]);
                        $education->field = htmlentities($field[$k]);
                        $education->access_id = $access;

                        if ($v == "new") {
                            $education_guids[] = $education->save();
                        } else {
                            $education->save();
                        }


                    }
                }
            }
            if ($user->education == NULL) {
                $user->education = $education_guids;
            }
            else {
                $stack = $user->education;
                if (!(is_array($stack))) { $stack = array($stack); }

                if ($education_guids != NULL) {
                    $user->education = array_merge($stack, $education_guids);
                }

            }

            $user->education_access = $access;
            $user->save();

            break;
        case 'work-experience':

            $work_experience = get_input('work');
            $edit = $work_experience['edit'];
            $delete = $work_experience['delete_guids'];
            $access = get_input('access');

            $experience_list = $user->work;

            if (!(is_array($delete))) { $delete = array($delete); }

            foreach ($delete as $delete_guid) {
                if ($delete_guid != NULL) {

                    if ($delete = get_entity($delete_guid)) {
                        $delete->delete();
                    }
                    if (is_array($experience_list)) {
                        if (($key = array_search($delete_guid, $experience_list)) !== false) {
                            unset($experience_list[$key]);
                        }
                    }
                    elseif ($experience_list == $delete_guid) {
                        $experience_list = null;
                    }
                }
            }

            $user->work = $experience_list;
            $work_experience_guids = array();

            if ($edit != null && !is_array($edit)) {
                $edit = array( $edit );
            }

           

            //create new work experience entries
            if ( is_array($edit) ) {
                foreach ($edit as $work) {

                    $validInput = true;
                    /*
                    if($work['ongoing'] == true){
                        $work['endyear'] = $work['startyear'];
                    }
                    */
                    //validation of work experience entry
                    if(trim($work['title']) == '' || trim($work['organization']) == ''){
                        $validInput = false;
                        $error = true;
                    }
                    /*
                    if(trim($work['endyear']) < trim($work['startyear'])){
                        $validInput = false;
                        $error = true;
                    }
                    */
                    if($validInput == true) {


                        if ($work['eguid'] == "new") {
                            $experience = new ElggObject();
                            $experience->subtype = "experience";
                            $experience->owner_guid = $user_guid;
                        } else {
                            $experience = get_entity($work['eguid']);
                        }

                        $experience->title = htmlentities($work['title']);
                        $experience->description = htmlentities($work['responsibilities']);

                        $experience->organization = htmlentities($work['organization']);
                        $experience->startdate = $work['startdate'];
                        $experience->startyear = $work['startyear'];
                        $experience->enddate = $work['enddate'];
                        $experience->endyear = $work['endyear'];
                        $experience->ongoing = $work['ongoing'];
                        $experience->responsibilities = trim($work['responsibilities']);
                        $experience->colleagues = $work['colleagues'];
                        $experience->access_id = $access;

                        if ($work['eguid'] == "new") {
                            $work_experience_guids[] = $experience->save();
                        } else {
                            $experience->save();
                        }

                    }
                }
            }

            if ($user->work == NULL) {
                $user->work = $work_experience_guids;
            }
            else {
                $stack = $user->work;
                if (!(is_array($stack))) { $stack = array($stack); }

                if ($work_experience_guids != NULL) {
                    $user->work = array_merge($stack, $work_experience_guids);
                }
            }
            $user->work_access = $access;
            $user->save();

            break;

        case 'skills':
            $skillsToAdd = get_input('skillsadded', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0021.');
            $skillsToRemove = get_input('skillsremoved', 'ERROR: Ask your admin to grep: 5FH13GAHHHS0022.');
            $access = ACCESS_LOGGED_IN;

            $skill_guids = array();

            foreach ($skillsToAdd as $new_skill) {
                $skill = new ElggObject();
                $skill->subtype = "MySkill";
                $skill->title = htmlentities($new_skill);
                $skill->owner_guid = $user_guid;
                $skill->access_id = $access;
                $skill->endorsements = NULL;
                $skill_guids[] = $skill->save();
            }

            $skill_list = $user->gc_skills;

            if (!(is_array($skill_list))) { $skill_list = array($skill_list); }
            if (!(is_array($skillsToRemove))) { $skillsToRemove = array($skillsToRemove); }

            foreach ($skillsToRemove as $remove_guid) {
                if ($remove_guid != NULL) {

                    if ($remove = get_entity($remove_guid)) {
                        $remove->delete();
                    }

                    if (($key = array_search($remove_guid, $skill_list)) !== false) {
                        unset($skill_list[$key]);
                    }
                }
            }

            $user->gc_skills = $skill_list;

            if ($user->gc_skills == NULL) {
                $user->gc_skills = $skill_guids;
            }
            else {
                $stack = $user->gc_skills;
                if (!(is_array($stack))) { $stack = array($stack); }

                if ($skill_guids != NULL) {
                    $user->gc_skills = array_merge($stack, $skill_guids);
                }
            }

            //$user->gc_skills = null; // dev stuff... delete me
            //$user->skillsupgraded = NULL; // dev stuff.. delete me
            $user->save();
            
            break;
        case 'old-skills':
            $user->skillsupgraded = TRUE;
            break;
        case 'languages':
            $firstlang = get_input('firstlang', '');
            $french = get_input('french', 'ERROR: Ask your admin to grep: ASFDJKGJKG333616.');
            $english = get_input('english', 'ERROR: Ask your admin to grep: SDFANLVNVNVNVNVNAA31566.');
            $languagesToAdd = get_input('langadded', 'ERROR: Ask your admin to grep: 5FH13FFSSGAHHHS0021.');
            $languagesToRemove = get_input('langremoved', 'ERROR: Ask your admin to grep: 5AAAAGGFH13GAH0022.');
            //$access = get_input('access');    // not used
			$access = get_input('access_id');
            $user->english = $english;
            $user->french = $french;
            $user->officialLanguage = $firstlang;

            $user->save();
			
			$metadata = elgg_get_metadata(array(
                'metadata_names' => array('english'),
                'metadata_owner_guids' => array(elgg_get_logged_in_user_guid()),

            ));
            if ($metadata){
                foreach ($metadata as $data){
					
                    update_metadata($data->id, $data->name, $data->value, $data->value_type, $data->owner_guid, $access);
                    //error_log('id '.$data->id .' name '. $data->name.' value '. $data->value.' value type '. $data->value_type.' owner_guid '.$data->owner_guid.' $access '. $access);
                }
                //$metadata[0]->save();
            }
            $metadata = elgg_get_metadata(array(
                'metadata_names' => array('french'),
                'metadata_owner_guids' => array(elgg_get_logged_in_user_guid()),

            ));
            if ($metadata){
                foreach ($metadata as $data){
                    
                    update_metadata($data->id, $data->name, $data->value, $data->value_type, $data->owner_guid, $access);
                }
                //$metadata[0]->save();
            }
			$metadata = elgg_get_metadata(array(
                'metadata_names' => array('officialLanguage'),
                'metadata_owner_guids' => array(elgg_get_logged_in_user_guid()),

            ));
            if ($metadata){
                foreach ($metadata as $data){
                    
                    update_metadata($data->id, $data->name, $data->value, $data->value_type, $data->owner_guid, $access);
                }
                //$metadata[0]->save();
            }
			
            break;
        case 'portfolio':
            $portfolio = get_input('portfolio');
            $edit = $portfolio['edit'];
            $delete = $portfolio['delete_guids'];
            $access = get_input('access');

            $portfolio_list = $user->portfolio;

            if (!(is_array($delete))) { $delete = array($delete); }

            foreach ($delete as $delete_guid) {
                if ($delete_guid != NULL) {

                    if ($delete = get_entity($delete_guid)) {
                        $delete->delete();
                    }
                    if (is_array($portfolio_list)) {
                        if (($key = array_search($delete_guid, $portfolio_list)) !== false) {
                            unset($portfolio_list[$key]);
                        }
                    }
                    elseif ($portfolio_list == $delete_guid) {
                        $portfolio_list = null;
                    }
                }
            }

            $user->portfolio = $portfolio_list;
            $portfolio_list_guids = array();

            //create new work experience entries
            foreach ($edit as $portfolio_edit) {

                $validInput = true;

                if(trim($portfolio_edit['title']) == '' || trim($portfolio_edit['description']) == '' || trim($portfolio_edit['link']) == ''){
                    $validInput = false;
                    $error = true;
                }

                if($portfolio_edit['datestamped'] == false && trim( $portfolio_edit['pubdate']) == ''){
                    $validInput = false;
                    $error = true;
                }

                if($validInput == true){

                    if ($portfolio_edit['eguid'] == "new") {
                        $entry = new ElggObject();
                        $entry->subtype = "portfolio";
                        $entry->owner_guid = $user_guid;
                    }
                    else {
                        $entry = get_entity($portfolio_edit['eguid']);
                    }

                    $entry->title = htmlentities($portfolio_edit['title']);
                    $entry->description = htmlentities($portfolio_edit['description']);

                    $entry->link = $portfolio_edit['link'];
                    $entry->pubdate = $portfolio_edit['pubdate'];
                    $entry->datestamped = $portfolio_edit['datestamped'];

                    $entry->access_id = $access;

                    if($portfolio_edit['eguid'] == "new") {
                        $portfolio_list_guids[] = $entry->save();
                    }
                    else {
                        $entry->save();
                    }
                }
            }

            if ($user->portfolio == NULL) {
                $user->portfolio = $portfolio_list_guids;
            }
            else {
                $stack = $user->portfolio;
                if (!(is_array($stack))) { $stack = array($stack); }

                if ($portfolio_list_guids != NULL) {
                    $user->portfolio = array_merge($stack, $portfolio_list_guids);
                }
            }
            //$user->portfolio = null;
            $user->portfolio_access = $access;
            $user->save();

            break;

        default:
            system_message(elgg_echo("profile:saved"));

    }

    //system_message(elgg_echo("profile:saved"));
    if($error == true){
       register_error(elgg_echo('Not all information could be saved, empty fields are not allowed'));
    } else {
        system_message(elgg_echo("profile:saved"));
    }

} else {

    // In case this view will be called via the elgg_view_form() action, then we know it's the basic profile only
}