import requests
import json
import matplotlib.pyplot as plt

params = [
	['native', 'raw', 1000],
	['native', 'raw', 5000],
	['native', 'raw', 7500],
	['native', 'raw', 10000],
	['managed', 'raw', 1000],
	['managed', 'raw', 5000],
	['managed', 'raw', 7500],
	['managed', 'raw', 10000]
]

urls = [
'http://dev.estore.local:8000/app_dev.php/api/persistances/{}/deserializations/{}/batches/{}/import'.format(
	param[0], param[1], str(param[2])
)
	for param in params
]

results = []

for key, url in enumerate(urls):
	print "[BENCHMARK][PERSISTANCE][{}][DESERIALIZATION][{}][BATCHSIZE][{}]".format(params[key][0], params[key][1], params[key][2])
	print "[URL][{}]".format(url)
	results.append(json.loads(requests.get(url).text))


xAxis = list(set([ param[2] for param in params]))
xAxis.sort()


managedResults = [result for result in results if result["strategy"] == 'managed']
nativeResults = [result for result in results if result["strategy"] == 'native']

managedMemUsage = [ managedResult["memUsage"] for managedResult in managedResults]
nativeMemUsage = [ nativeResult["memUsage"] for nativeResult in nativeResults]

managedTimeElapsed = [ managedResult["persistanceTimeElapsed"] for managedResult in managedResults]
nativeTimeElapsed = [ nativeResult["persistanceTimeElapsed"] for nativeResult in nativeResults]

nativeMemUsage.sort()
managedMemUsage.sort()

managedTimeElapsed.sort()
nativeTimeElapsed.sort()

plt.plot(xAxis, nativeTimeElapsed)
plt.plot(xAxis, managedTimeElapsed)
plt.show()